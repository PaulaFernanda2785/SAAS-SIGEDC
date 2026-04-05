<?php

declare(strict_types=1);

namespace App\Controllers\Operational;

use App\Services\Files\OperationalDocumentService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;

final class DocumentController
{
    public function __construct(private readonly ?OperationalDocumentService $service = null)
    {
    }

    public function index(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $data = ($this->service ?? new OperationalDocumentService())->workspaceData($auth, $request->all());

        return Response::view('operational/documents', [
            'title' => 'Documentos Operacionais',
            'auth' => $auth,
            'scope' => $data['scope'],
            'filters' => $data['filters'],
            'attachments' => $data['attachments'],
            'attachmentsByEntity' => $data['attachments_by_entity'],
            'incidentOptions' => $data['incident_options'],
            'planconOptions' => $data['plancon_options'],
            'incidentRecordOptions' => $data['incident_record_options'],
            'planconRiskOptions' => $data['plancon_risk_options'],
        ], 'operational');
    }

    public function upload(Request $request): Response
    {
        $result = ($this->service ?? new OperationalDocumentService())->upload(
            $_SESSION['auth'] ?? [],
            $request->all(),
            is_array($_FILES['arquivo'] ?? null) ? $_FILES['arquivo'] : null,
            $request
        );

        if (($result['ok'] ?? false) === true) {
            Flash::set('success', (string) ($result['message'] ?? 'Documento anexado com sucesso.'));
        } else {
            Flash::set('error', (string) ($result['message'] ?? 'Falha ao anexar documento.'));
        }

        return Response::redirect('/operational/documentos');
    }

    public function download(Request $request): Response
    {
        $attachmentId = (int) $request->input('anexo_id', 0);
        $result = ($this->service ?? new OperationalDocumentService())
            ->download($_SESSION['auth'] ?? [], $attachmentId, $request);

        if (($result['ok'] ?? false) !== true) {
            Flash::set('error', (string) ($result['message'] ?? 'Falha ao baixar documento.'));
            return Response::redirect('/operational/documentos');
        }

        return Response::file(
            (string) $result['file_path'],
            (string) $result['download_name'],
            (string) $result['mime_type'],
            false
        );
    }
}
