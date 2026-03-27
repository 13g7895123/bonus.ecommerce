<?php

namespace App\Controllers;

use App\Repositories\ApiLogRepository;
use App\Repositories\ThirdPartyLogRepository;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * SadminController — 超級管理後台，顯示所有 API 紀錄（含第三方外部呼叫）
 *
 * 路由前綴: /api/v1/sadmin
 * 本控制器不要求登入，與 AdminPanelController 相同設計（內部工具）。
 */
class SadminController extends Controller
{
    private function json(mixed $data, int $code = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($code)
            ->setContentType('application/json')
            ->setJSON($data);
    }

    // ── Stats ─────────────────────────────────────────────────────────────────

    public function stats(): ResponseInterface
    {
        $apiRepo = new ApiLogRepository();
        $tpRepo  = new ThirdPartyLogRepository();

        $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));

        $totalApi   = $apiRepo->paginate(1, 1);
        $recentApi  = $apiRepo->paginate(1, 1, ['date_from' => $yesterday]);
        $totalTp    = $tpRepo->paginate(1, 1);
        $recentTp   = $tpRepo->paginate(1, 1, ['date_from' => $yesterday]);
        $failedTp   = $tpRepo->paginate(1, 1, ['date_from' => $yesterday, 'success' => '0']);

        return $this->json([
            'total_api_logs'         => $totalApi['total'],
            'api_requests_24h'       => $recentApi['total'],
            'total_third_party_logs' => $totalTp['total'],
            'third_party_calls_24h'  => $recentTp['total'],
            'third_party_failures_24h' => $failedTp['total'],
        ]);
    }

    // ── Incoming API Logs ─────────────────────────────────────────────────────

    public function apiLogs(): ResponseInterface
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $limit   = min((int) ($this->request->getGet('limit') ?? 20), 100);
        $filters = array_filter([
            'method'        => $this->request->getGet('method'),
            'uri'           => $this->request->getGet('uri'),
            'user_id'       => $this->request->getGet('user_id'),
            'response_code' => $this->request->getGet('response_code'),
            'date_from'     => $this->request->getGet('date_from'),
            'date_to'       => $this->request->getGet('date_to'),
        ]);

        $result = (new ApiLogRepository())->paginate($page, $limit, $filters);

        return $this->json(['page' => $page, 'limit' => $limit, ...$result]);
    }

    public function apiLogDetail(int $id): ResponseInterface
    {
        $log = model(\App\Models\ApiLogModel::class)->find($id);
        if (!$log) {
            return $this->json(['message' => 'Log not found'], 404);
        }
        if (is_string($log['request_headers'])) {
            $log['request_headers'] = json_decode($log['request_headers'], true);
        }
        return $this->json($log);
    }

    // ── Third-Party API Logs ──────────────────────────────────────────────────

    public function thirdPartyLogs(): ResponseInterface
    {
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $limit   = min((int) ($this->request->getGet('limit') ?? 20), 100);
        $filters = [];

        if ($s = $this->request->getGet('service'))       $filters['service']       = $s;
        if ($a = $this->request->getGet('action'))        $filters['action']        = $a;
        if ($c = $this->request->getGet('response_code')) $filters['response_code'] = $c;
        if (($ok = $this->request->getGet('success')) !== null) $filters['success'] = $ok;
        if ($df = $this->request->getGet('date_from'))    $filters['date_from']     = $df;
        if ($dt = $this->request->getGet('date_to'))      $filters['date_to']       = $dt;

        $result = (new ThirdPartyLogRepository())->paginate($page, $limit, $filters);

        return $this->json(['page' => $page, 'limit' => $limit, ...$result]);
    }

    public function thirdPartyLogDetail(int $id): ResponseInterface
    {
        $log = model(\App\Models\ThirdPartyLogModel::class)->find($id);
        if (!$log) {
            return $this->json(['message' => 'Log not found'], 404);
        }
        return $this->json($log);
    }
}
