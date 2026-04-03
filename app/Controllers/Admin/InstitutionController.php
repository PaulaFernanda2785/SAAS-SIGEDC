<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\SaaS\InstitutionRepository;
use App\Services\Audit\AuditService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;
use Throwable;

final class InstitutionController
{
    public function __construct(
        private readonly ?InstitutionRepository $institutionRepository = null,
        private readonly ?AuditService $auditService = null
    ) {
    }

    public function index(Request $request): Response
    {
        $repository = $this->institutionRepository ?? new InstitutionRepository();

        return Response::view('admin/institutions', [
            'title' => 'Gestao Institucional',
            'auth' => $_SESSION['auth'] ?? [],
            'accounts' => $repository->accounts(),
            'orgaos' => $repository->orgaos(),
            'unidades' => $repository->unidades(),
            'usuarios' => $repository->usuarios(),
            'perfis' => $repository->perfis(),
            'vinculos' => $repository->vinculosUsuarioPerfil(),
            'options' => $repository->contextOptions(),
        ], 'admin');
    }

    public function storeAccount(Request $request): Response
    {
        $nomeFantasia = trim((string) $request->input('nome_fantasia', ''));
        if ($nomeFantasia === '') {
            Flash::set('error', 'Informe o nome fantasia da conta.');
            return Response::redirect('/admin/institucional');
        }

        try {
            $id = ($this->institutionRepository ?? new InstitutionRepository())->createAccount([
                'nome_fantasia' => $nomeFantasia,
                'razao_social' => $this->nullableText($request->input('razao_social')),
                'cpf_cnpj' => $this->nullableText($request->input('cpf_cnpj')),
                'email_principal' => $this->nullableText($request->input('email_principal')),
                'status_cadastral' => $this->sanitizeEnum((string) $request->input('status_cadastral', 'ATIVA'), ['ATIVA', 'INATIVA', 'BLOQUEADA'], 'ATIVA'),
            ]);

            $this->audit('CONTAS', 'CONTA_CREATE', 'contas', $id, ['nome_fantasia' => $nomeFantasia], $request);
            Flash::set('success', 'Conta cadastrada com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar conta. Verifique dados duplicados.');
        }

        return Response::redirect('/admin/institucional');
    }

    public function storeOrgao(Request $request): Response
    {
        $contaId = (int) $request->input('conta_id', 0);
        $nomeOficial = trim((string) $request->input('nome_oficial', ''));
        if ($contaId < 1 || $nomeOficial === '') {
            Flash::set('error', 'Informe conta e nome do orgao.');
            return Response::redirect('/admin/institucional');
        }

        try {
            $id = ($this->institutionRepository ?? new InstitutionRepository())->createOrgao([
                'conta_id' => $contaId,
                'nome_oficial' => $nomeOficial,
                'sigla' => $this->nullableText($request->input('sigla')),
                'cnpj' => $this->nullableText($request->input('cnpj')),
                'status_orgao' => $this->sanitizeEnum((string) $request->input('status_orgao', 'ATIVO'), ['ATIVO', 'INATIVO', 'BLOQUEADO'], 'ATIVO'),
            ]);

            $this->audit('ORGAOS', 'ORGAO_CREATE', 'orgaos', $id, ['conta_id' => $contaId], $request);
            Flash::set('success', 'Orgao cadastrado com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar orgao.');
        }

        return Response::redirect('/admin/institucional');
    }

    public function storeUnidade(Request $request): Response
    {
        $orgaoId = (int) $request->input('orgao_id', 0);
        $nomeUnidade = trim((string) $request->input('nome_unidade', ''));
        if ($orgaoId < 1 || $nomeUnidade === '') {
            Flash::set('error', 'Informe orgao e nome da unidade.');
            return Response::redirect('/admin/institucional');
        }

        try {
            $id = ($this->institutionRepository ?? new InstitutionRepository())->createUnidade([
                'orgao_id' => $orgaoId,
                'unidade_superior_id' => $this->nullableInt($request->input('unidade_superior_id')),
                'codigo_unidade' => $this->nullableText($request->input('codigo_unidade')),
                'nome_unidade' => $nomeUnidade,
                'tipo_unidade' => $this->nullableText($request->input('tipo_unidade')),
                'status_unidade' => $this->sanitizeEnum((string) $request->input('status_unidade', 'ATIVA'), ['ATIVA', 'INATIVA'], 'ATIVA'),
            ]);

            $this->audit('UNIDADES', 'UNIDADE_CREATE', 'unidades', $id, ['orgao_id' => $orgaoId], $request);
            Flash::set('success', 'Unidade cadastrada com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar unidade.');
        }

        return Response::redirect('/admin/institucional');
    }

    public function storeUsuario(Request $request): Response
    {
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

        try {
            $id = ($this->institutionRepository ?? new InstitutionRepository())->createUsuario([
                'conta_id' => $contaId,
                'orgao_id' => $orgaoId,
                'unidade_id' => $this->nullableInt($request->input('unidade_id')),
                'nome_completo' => $nomeCompleto,
                'email_login' => $emailLogin,
                'matricula_funcional' => $this->nullableText($request->input('matricula_funcional')),
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'status_usuario' => $this->sanitizeEnum((string) $request->input('status_usuario', 'ATIVO'), ['ATIVO', 'INATIVO', 'BLOQUEADO'], 'ATIVO'),
            ]);

            $perfilId = (int) $request->input('perfil_id', 0);
            if ($perfilId > 0) {
                ($this->institutionRepository ?? new InstitutionRepository())->vincularPerfilAoUsuario($id, $perfilId);
            }

            $this->audit('USUARIOS', 'USUARIO_CREATE', 'usuarios', $id, ['conta_id' => $contaId, 'orgao_id' => $orgaoId], $request);
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
        $usuarioId = (int) $request->input('usuario_id', 0);
        $perfilId = (int) $request->input('perfil_id', 0);

        if ($usuarioId < 1 || $perfilId < 1) {
            Flash::set('error', 'Informe usuario e perfil para vinculo.');
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
