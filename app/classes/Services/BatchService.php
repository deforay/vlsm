<?php


namespace App\Services;

use Generator;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;

final class BatchService
{

    protected ?DatabaseService $db;

    public function __construct(?DatabaseService $db)
    {
        $this->db = $db ?? ContainerRegistry::get(DatabaseService::class);
    }

    public function doesBatchCodeExist($code)
    {
        $this->db->where("batch_code", $code);
        return $this->db->getOne("batch_details");
    }

    public function createBatchCode(): array
    {
        $batchQuery = 'SELECT IFNULL(MAX(batch_code_key), 0) + 1 AS maxId
                        FROM batch_details as bd
                        WHERE DATE(bd.request_created_datetime) = CURRENT_DATE';
        $batchResult = $this->db->rawQueryOne($batchQuery);

        $batchCode  = date('Ymd') . sprintf("%03s", $batchResult['maxId']);

        return [$batchResult['maxId'], $batchCode];
    }


    public function excelColumnRange($lower, $upper): Generator
    {
        ++$upper;
        for ($i = $lower; $i !== $upper; ++$i) {
            yield $i;
        }
    }
}
