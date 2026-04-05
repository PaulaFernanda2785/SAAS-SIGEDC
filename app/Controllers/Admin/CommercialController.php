<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Domain\Enum\BrazilUf;
use App\Domain\Enum\UserProfile;
use App\Repositories\SaaS\CommercialRepository;
use App\Repositories\SaaS\InstitutionRepository;
use App\Services\Audit\AuditService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;
use Throwable;

final class CommercialController
{
    public function __construct(
        private readonly ?CommercialRepository $commercialRepository = null,
        private readonly ?InstitutionRepository $institutionRepository = null,
        private readonly ?AuditService $auditService = null
    ) {
    }

    public function index(Request $request): Response
    {
        $commercialRepository = $this->commercialRepository ?? new CommercialRepository();
        $institutionRepository = $this->institutionRepository ?? new InstitutionRepository();
        $auth = $_SESSION['auth'] ?? [];
        $currentUf = $this->resolveUfFilter($request, $auth);

        return Response::view('admin/commercial', [
            'title' => 'Gestao Comercial SaaS',
            'auth' => $auth,
            'plans' => $commercialRepository->plans(),
            'assinaturas' => $commercialRepository->assinaturas($currentUf),
            'modulos' => $commercialRepository->modules(),
            'modulosLiberados' => $commercialRepository->modulosPorAssinatura($currentUf),
            'contas' => $institutionRepository->accounts($currentUf),
            'options' => $institutionRepository->contextOptions($currentUf),
            'currentUfFilter' => $currentUf,
            'canSelectAllUf' => $this->isAdminMaster($auth),
        ], 'admin');
    }

    public function storePlan(Request $request): Response
    {
        $codigoPlano = strtoupper(trim((string) $request->input('codigo_plano', '')));
        $nomePlano = trim((string) $request->input('nome_plano', ''));

        if ($codigoPlano === '' || $nomePlano === '') {
            Flash::set('error', 'Informe codigo e nome do plano.');
            return Response::redirect('/admin/comercial');
        }

        $precoBruto = str_replace(',', '.', trim((string) $request->input('preco_mensal', '0')));
        $precoMensal = is_numeric($precoBruto) ? (float) $precoBruto : 0.0;

        try {
            $id = ($this->commercialRepository ?? new CommercialRepository())->createPlan([
                'codigo_plano' => $codigoPlano,
                'nome_plano' => $nomePlano,
                'descricao' => $this->nullableText($request->input('descricao')),
                'preco_mensal' => number_format($precoMensal, 2, '.', ''),
                'limite_usuarios' => $this->nullableInt($request->input('limite_usuarios')),
                'status_plano' => $this->sanitizeEnum((string) $request->input('status_plano', 'ATIVO'), ['ATIVO', 'INATIVO'], 'ATIVO'),
            ]);

            $this->audit('PLANOS', 'PLANO_CREATE', 'planos_catalogo', $id, ['codigo_plano' => $codigoPlano], $request);
            Flash::set('success', 'Plano cadastrado com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar plano. Verifique duplicidade de codigo/nome.');
        }

        return Response::redirect('/admin/comercial');
    }

    public function storeAssinatura(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $institutionRepository = $this->institutionRepository ?? new InstitutionRepository();

        $contaId = (int) $request->input('conta_id', 0);
        $planoId = (int) $request->input('plano_id', 0);
        $iniciaEm = trim((string) $request->input('inicia_em', ''));

        if ($contaId < 1 || $planoId < 1 || $iniciaEm === '') {
            Flash::set('error', 'Informe conta, plano e data de inicio da assinatura.');
            return Response::redirect('/admin/comercial');
        }

        $conta = $institutionRepository->accountById($contaId);
        if ($conta === null) {
            Flash::set('error', 'Conta selecionada nao encontrada.');
            return Response::redirect('/admin/comercial');
        }

        $ufSigla = BrazilUf::normalize($conta['uf_sigla'] ?? null);
        if ($ufSigla === null) {
            Flash::set('error', 'Conta selecionada sem UF de origem. Atualize o cadastro da conta.');
            return Response::redirect('/admin/comercial');
        }
        if (!$this->canOperateUf($auth, $ufSigla)) {
            Flash::set('error', 'Seu perfil administrativo nao pode operar fora do UF de contexto.');
            return Response::redirect('/admin/comercial');
        }

        try {
            $id = ($this->commercialRepository ?? new CommercialRepository())->createAssinatura([
                'conta_id' => $contaId,
                'uf_sigla' => $ufSigla,
                'plano_id' => $planoId,
                'status_assinatura' => $this->sanitizeEnum((string) $request->input('status_assinatura', 'TRIAL'), ['TRIAL', 'ATIVA', 'SUSPENSA', 'CANCELADA', 'EXPIRADA'], 'TRIAL'),
                'inicia_em' => $iniciaEm,
                'expira_em' => $this->nullableDate($request->input('expira_em')),
                'trial_fim_em' => $this->nullableDate($request->input('trial_fim_em')),
                'motivo_status' => $this->nullableText($request->input('motivo_status')),
            ]);

            $moduleIds = $request->input('modulos', []);
            if (is_array($moduleIds)) {
                foreach ($moduleIds as $moduleId) {
                    $moduleId = (int) $moduleId;
                    if ($moduleId > 0) {
                        ($this->commercialRepository ?? new CommercialRepository())->liberarModulo($id, $moduleId, 'ATIVA');
                    }
                }
            }

            $this->audit(
                'ASSINATURAS',
                'ASSINATURA_CREATE',
                'assinaturas',
                $id,
                ['conta_id' => $contaId, 'plano_id' => $planoId, 'uf_sigla' => $ufSigla],
                $request
            );
            Flash::set('success', 'Assinatura cadastrada com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar assinatura.');
        }

        return Response::redirect('/admin/comercial');
    }

    public function upsertModulo(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $assinaturaId = (int) $request->input('assinatura_id', 0);
        $moduloId = (int) $request->input('modulo_id', 0);
        $statusLiberacao = $this->sanitizeEnum((string) $request->input('status_liberacao', 'ATIVA'), ['ATIVA', 'BLOQUEADA'], 'ATIVA');

        if ($assinaturaId < 1 || $moduloId < 1) {
            Flash::set('error', 'Informe assinatura e modulo para atualizar a liberacao.');
            return Response::redirect('/admin/comercial');
        }

        $assinatura = ($this->commercialRepository ?? new CommercialRepository())->assinaturaById($assinaturaId);
        if ($assinatura === null) {
            Flash::set('error', 'Assinatura selecionada nao encontrada.');
            return Response::redirect('/admin/comercial');
        }
        if (!$this->canOperateUf($auth, BrazilUf::normalize($assinatura['uf_sigla'] ?? null))) {
            Flash::set('error', 'Seu perfil administrativo nao pode alterar modulos fora do UF de contexto.');
            return Response::redirect('/admin/comercial');
        }

        try {
            ($this->commercialRepository ?? new CommercialRepository())->liberarModulo($assinaturaId, $moduloId, $statusLiberacao);
            $this->audit(
                'ASSINATURAS',
                'ASSINATURA_MODULO_UPSERT',
                'assinaturas_modulos',
                null,
                ['assinatura_id' => $assinaturaId, 'modulo_id' => $moduloId, 'status_liberacao' => $statusLiberacao],
                $request
            );
            Flash::set('success', 'Liberacao de modulo atualizada.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao atualizar liberacao de modulo.');
        }

        return Response::redirect('/admin/comercial');
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

    private function nullableDate(mixed $value): ?string
    {
        $date = trim((string) $value);
        return $date === '' ? null : $date;
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
