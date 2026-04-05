<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Domain\Enum\BrazilUf;
use App\Domain\Enum\UserProfile;
use App\Repositories\SaaS\InstitutionRepository;
use App\Repositories\Territory\TerritoryRepository;
use App\Services\Audit\AuditService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;
use Throwable;

final class InstitutionController
{
    public function __construct(
        private readonly ?InstitutionRepository $institutionRepository = null,
        private readonly ?TerritoryRepository $territoryRepository = null,
        private readonly ?AuditService $auditService = null
    ) {
    }

    public function index(Request $request): Response
    {
        $repository = $this->institutionRepository ?? new InstitutionRepository();
        $auth = $_SESSION['auth'] ?? [];
        $isAdminMaster = $this->isAdminMaster($auth);
        $currentUf = $this->resolveUfFilter($request, $auth);

        return Response::view('admin/institutions', [
            'title' => 'Gestao Institucional',
            'auth' => $auth,
            'accounts' => $repository->accounts($currentUf),
            'orgaos' => $repository->orgaos($currentUf),
            'unidades' => $repository->unidades($currentUf),
            'usuarios' => $repository->usuarios($currentUf),
            'perfis' => $repository->perfis(),
            'vinculos' => $repository->vinculosUsuarioPerfil($currentUf),
            'options' => $repository->contextOptions($currentUf),
            'currentUfFilter' => $currentUf,
            'canSelectAllUf' => $isAdminMaster,
        ], 'admin');
    }

    public function storeAccount(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $nomeFantasia = trim((string) $request->input('nome_fantasia', ''));
        if ($nomeFantasia === '') {
            Flash::set('error', 'Informe o nome fantasia da conta.');
            return Response::redirect('/admin/institucional');
        }

        $ufSigla = BrazilUf::normalize($request->input('uf_sigla'));
        if (!$this->isAdminMaster($auth)) {
            $ufSigla = BrazilUf::normalize($auth['uf_sigla'] ?? null);
        }

        if ($ufSigla === null) {
            Flash::set('error', 'Selecione o UF de origem da conta.');
            return Response::redirect('/admin/institucional');
        }

        if (!$this->canOperateUf($auth, $ufSigla)) {
            Flash::set('error', 'Seu perfil administrativo nao pode operar fora do UF de contexto.');
            return Response::redirect('/admin/institucional');
        }

        if (!(($this->territoryRepository ?? new TerritoryRepository())->ufExists($ufSigla))) {
            Flash::set('error', 'UF de origem invalido. Atualize a base territorial primeiro.');
            return Response::redirect('/admin/institucional');
        }

        try {
            $id = ($this->institutionRepository ?? new InstitutionRepository())->createAccount([
                'nome_fantasia' => $nomeFantasia,
                'razao_social' => $this->nullableText($request->input('razao_social')),
                'cpf_cnpj' => $this->nullableText($request->input('cpf_cnpj')),
                'uf_sigla' => $ufSigla,
                'email_principal' => $this->nullableText($request->input('email_principal')),
                'status_cadastral' => $this->sanitizeEnum((string) $request->input('status_cadastral', 'ATIVA'), ['ATIVA', 'INATIVA', 'BLOQUEADA'], 'ATIVA'),
            ]);

            $this->audit('CONTAS', 'CONTA_CREATE', 'contas', $id, ['nome_fantasia' => $nomeFantasia, 'uf_sigla' => $ufSigla], $request);
            Flash::set('success', 'Conta cadastrada com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar conta. Verifique dados duplicados.');
        }

        return Response::redirect('/admin/institucional');
    }

    public function storeOrgao(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $contaId = (int) $request->input('conta_id', 0);
        $nomeOficial = trim((string) $request->input('nome_oficial', ''));
        if ($contaId < 1 || $nomeOficial === '') {
            Flash::set('error', 'Informe conta e nome do orgao.');
            return Response::redirect('/admin/institucional');
        }

        $conta = ($this->institutionRepository ?? new InstitutionRepository())->accountById($contaId);
        if ($conta === null) {
            Flash::set('error', 'Conta selecionada nao encontrada.');
            return Response::redirect('/admin/institucional');
        }

        $ufSigla = BrazilUf::normalize($conta['uf_sigla'] ?? null);
        if ($ufSigla === null) {
            Flash::set('error', 'Conta selecionada sem UF de origem. Atualize o cadastro da conta.');
            return Response::redirect('/admin/institucional');
        }

        if (!$this->canOperateUf($auth, $ufSigla)) {
            Flash::set('error', 'Seu perfil administrativo nao pode operar fora do UF de contexto.');
            return Response::redirect('/admin/institucional');
        }

        try {
            $id = ($this->institutionRepository ?? new InstitutionRepository())->createOrgao([
                'conta_id' => $contaId,
                'nome_oficial' => $nomeOficial,
                'sigla' => $this->nullableText($request->input('sigla')),
                'cnpj' => $this->nullableText($request->input('cnpj')),
                'uf_sigla' => $ufSigla,
                'status_orgao' => $this->sanitizeEnum((string) $request->input('status_orgao', 'ATIVO'), ['ATIVO', 'INATIVO', 'BLOQUEADO'], 'ATIVO'),
            ]);

            $this->audit('ORGAOS', 'ORGAO_CREATE', 'orgaos', $id, ['conta_id' => $contaId, 'uf_sigla' => $ufSigla], $request);
            Flash::set('success', 'Orgao cadastrado com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar orgao.');
        }

        return Response::redirect('/admin/institucional');
    }

    public function storeUnidade(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $orgaoId = (int) $request->input('orgao_id', 0);
        $nomeUnidade = trim((string) $request->input('nome_unidade', ''));
        if ($orgaoId < 1 || $nomeUnidade === '') {
            Flash::set('error', 'Informe orgao e nome da unidade.');
            return Response::redirect('/admin/institucional');
        }

        $orgao = ($this->institutionRepository ?? new InstitutionRepository())->orgaoById($orgaoId);
        if ($orgao === null) {
            Flash::set('error', 'Orgao selecionado nao encontrado.');
            return Response::redirect('/admin/institucional');
        }

        $ufSigla = BrazilUf::normalize($orgao['uf_sigla'] ?? null);
        if ($ufSigla === null) {
            Flash::set('error', 'Orgao selecionado sem UF de origem. Ajuste o cadastro do orgao.');
            return Response::redirect('/admin/institucional');
        }

        if (!$this->canOperateUf($auth, $ufSigla)) {
            Flash::set('error', 'Seu perfil administrativo nao pode operar fora do UF de contexto.');
            return Response::redirect('/admin/institucional');
        }

        try {
            $id = ($this->institutionRepository ?? new InstitutionRepository())->createUnidade([
                'orgao_id' => $orgaoId,
                'unidade_superior_id' => $this->nullableInt($request->input('unidade_superior_id')),
                'codigo_unidade' => $this->nullableText($request->input('codigo_unidade')),
                'nome_unidade' => $nomeUnidade,
                'tipo_unidade' => $this->nullableText($request->input('tipo_unidade')),
                'uf_sigla' => $ufSigla,
                'status_unidade' => $this->sanitizeEnum((string) $request->input('status_unidade', 'ATIVA'), ['ATIVA', 'INATIVA'], 'ATIVA'),
            ]);

            $this->audit('UNIDADES', 'UNIDADE_CREATE', 'unidades', $id, ['orgao_id' => $orgaoId, 'uf_sigla' => $ufSigla], $request);
            Flash::set('success', 'Unidade cadastrada com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar unidade.');
        }

        return Response::redirect('/admin/institucional');
    }

    public function storeUsuario(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $contaId = (int) $request->input('conta_id', 0);
        $orgaoId = (int) $request->input('orgao_id', 0);
        $nomeCompleto = trim((string) $request->input('nome_completo', ''));
        $emailLogin = trim(strtolower((string) $request->input('email_login', '')));
        $password = (string) $request->input('password', '');
        $minLength = (int) config('auth.password_min_length', 8);

        if ($contaId < 1 || $orgaoId < 1 || $nomeCompleto === '' || $emailLogin === '' || $password === '') {
            Flash::set('error', 'Preencha conta, orgao, nome, login e senha do usuario.');
            return Response::redirect('/admin/institucional');
        }

        if (strlen($password) < $minLength) {
            Flash::set('error', "A senha deve ter no minimo {$minLength} caracteres.");
            return Response::redirect('/admin/institucional');
        }

        $repository = $this->institutionRepository ?? new InstitutionRepository();
        $conta = $repository->accountById($contaId);
        $orgao = $repository->orgaoById($orgaoId);
        if ($conta === null || $orgao === null) {
            Flash::set('error', 'Conta ou orgao informado nao encontrado.');
            return Response::redirect('/admin/institucional');
        }
        if ((int) ($orgao['conta_id'] ?? 0) !== $contaId) {
            Flash::set('error', 'Orgao nao pertence a conta selecionada.');
            return Response::redirect('/admin/institucional');
        }

        $ufConta = BrazilUf::normalize($conta['uf_sigla'] ?? null);
        $ufOrgao = BrazilUf::normalize($orgao['uf_sigla'] ?? null);
        if ($ufConta === null || $ufOrgao === null || $ufConta !== $ufOrgao) {
            Flash::set('error', 'Conta e orgao precisam estar alinhados ao mesmo UF de origem.');
            return Response::redirect('/admin/institucional');
        }
        if (!$this->canOperateUf($auth, $ufOrgao)) {
            Flash::set('error', 'Seu perfil administrativo nao pode operar fora do UF de contexto.');
            return Response::redirect('/admin/institucional');
        }

        try {
            $id = $repository->createUsuario([
                'conta_id' => $contaId,
                'orgao_id' => $orgaoId,
                'unidade_id' => $this->nullableInt($request->input('unidade_id')),
                'uf_sigla' => $ufOrgao,
                'nome_completo' => $nomeCompleto,
                'email_login' => $emailLogin,
                'matricula_funcional' => $this->nullableText($request->input('matricula_funcional')),
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'status_usuario' => $this->sanitizeEnum((string) $request->input('status_usuario', 'ATIVO'), ['ATIVO', 'INATIVO', 'BLOQUEADO'], 'ATIVO'),
            ]);

            $perfilId = (int) $request->input('perfil_id', 0);
            if ($perfilId > 0) {
                $repository->vincularPerfilAoUsuario($id, $perfilId);
            }

            $this->audit(
                'USUARIOS',
                'USUARIO_CREATE',
                'usuarios',
                $id,
                ['conta_id' => $contaId, 'orgao_id' => $orgaoId, 'uf_sigla' => $ufOrgao],
                $request
            );
            Flash::set('success', 'Usuario cadastrado com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar usuario. Verifique login/matricula duplicados.');
        }

        return Response::redirect('/admin/institucional');
    }

    public function storePerfil(Request $request): Response
    {
        $nomePerfil = strtoupper(trim((string) $request->input('nome_perfil', '')));
        if ($nomePerfil === '') {
            Flash::set('error', 'Informe o nome do perfil.');
            return Response::redirect('/admin/institucional');
        }

        try {
            $id = ($this->institutionRepository ?? new InstitutionRepository())->createPerfil([
                'nome_perfil' => $nomePerfil,
                'descricao' => $this->nullableText($request->input('descricao')),
                'status_perfil' => $this->sanitizeEnum((string) $request->input('status_perfil', 'ATIVO'), ['ATIVO', 'INATIVO'], 'ATIVO'),
            ]);

            $this->audit('PERFIS', 'PERFIL_CREATE', 'perfis', $id, ['nome_perfil' => $nomePerfil], $request);
            Flash::set('success', 'Perfil cadastrado com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar perfil.');
        }

        return Response::redirect('/admin/institucional');
    }

    public function attachPerfil(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $usuarioId = (int) $request->input('usuario_id', 0);
        $perfilId = (int) $request->input('perfil_id', 0);

        if ($usuarioId < 1 || $perfilId < 1) {
            Flash::set('error', 'Informe usuario e perfil para vinculo.');
            return Response::redirect('/admin/institucional');
        }

        $usuario = ($this->institutionRepository ?? new InstitutionRepository())->usuarioById($usuarioId);
        if ($usuario === null) {
            Flash::set('error', 'Usuario selecionado nao encontrado.');
            return Response::redirect('/admin/institucional');
        }
        if (!$this->canOperateUf($auth, BrazilUf::normalize($usuario['uf_sigla'] ?? null))) {
            Flash::set('error', 'Seu perfil administrativo nao pode vincular perfis fora do UF de contexto.');
            return Response::redirect('/admin/institucional');
        }

        try {
            ($this->institutionRepository ?? new InstitutionRepository())->vincularPerfilAoUsuario($usuarioId, $perfilId);
            $this->audit('PERFIS', 'USUARIO_PERFIL_BIND', 'usuarios_perfis', null, ['usuario_id' => $usuarioId, 'perfil_id' => $perfilId], $request);
            Flash::set('success', 'Vinculo usuario-perfil atualizado.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao vincular usuario e perfil.');
        }

        return Response::redirect('/admin/institucional');
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function nullableInt(mixed $value): ?int
    {
        $intValue = (int) $value;
        return $intValue > 0 ? $intValue : null;
    }

    private function sanitizeEnum(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function resolveUfFilter(Request $request, array $auth): ?string
    {
        $requestedUf = BrazilUf::normalize($request->input('uf'));
        if ($this->isAdminMaster($auth)) {
            return $requestedUf;
        }

        return BrazilUf::normalize($auth['uf_sigla'] ?? null);
    }

    private function isAdminMaster(array $auth): bool
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        return in_array(UserProfile::ADMIN_MASTER, $profiles, true);
    }

    private function canOperateUf(array $auth, ?string $targetUf): bool
    {
        if ($this->isAdminMaster($auth)) {
            return true;
        }

        $userUf = BrazilUf::normalize($auth['uf_sigla'] ?? null);
        if ($userUf === null || $targetUf === null) {
            return false;
        }

        return $userUf === $targetUf;
    }

    private function audit(string $modulo, string $acao, string $entidadeTipo, ?int $entidadeId, array $detalhes, Request $request): void
    {
        ($this->auditService ?? new AuditService())->log([
            'conta_id' => $_SESSION['auth']['conta_id'] ?? null,
            'orgao_id' => $_SESSION['auth']['orgao_id'] ?? null,
            'unidade_id' => $_SESSION['auth']['unidade_id'] ?? null,
            'usuario_id' => $_SESSION['auth']['usuario_id'] ?? null,
            'modulo_codigo' => $modulo,
            'acao' => $acao,
            'resultado' => 'SUCESSO',
            'entidade_tipo' => $entidadeTipo,
            'entidade_id' => $entidadeId,
            'detalhes' => $detalhes,
            'ip_address' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
