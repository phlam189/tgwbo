<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoicesExport implements FromArray, WithHeadings, WithCustomCsvSettings
{
    protected $data;
    public $headingMapping;

    public function __construct()
    {

        $this->headingMapping = [
            'account_number' => trans('invoice.account_number'),
            'date' => trans('invoice.date'),
            'number_trans' => trans('invoice.number_trans'),
            'amount' => trans('invoice.amount'),
            'transfer_amount' => trans('invoice.transfer_amount'),
            'settlement_amount' => trans('invoice.settlement_amount'),
            'settlement_fee' => trans('invoice.settlement_fee'),
            'number_refunds' => trans('invoice.number_refunds'),
            'refund_amount' => trans('invoice.refund_amount'),
            'refund_fee' => trans('invoice.refund_fee'),
            'system_usage_fee' => trans('invoice.system_usage_fee'),
            'system_usage_fee_2' => trans('invoice.system_usage_fee_2'),
            'account_balance' => trans('invoice.account_balance'),
            'memo' => trans('invoice.memo'),
            'account_number_2' => trans('invoice.account_number_2'),
            'number_trans_2' => trans('invoice.number_trans_2'),
            'amount_2' => trans('invoice.amount_2'),
            'commission_bank_fee_2' => trans('invoice.withdrawal_fee_2'),
            'transfer_amount_2' => trans('invoice.transfer_amount_2'),
            'settlement_amount_2' => trans('invoice.settlement_amount_2'),
            'settlement_fee_2' => trans('invoice.settlement_fee_2'),
            'number_refunds_2' => trans('invoice.number_refunds_2'),
            'refund_amount_2' => trans('invoice.refund_amount_2'),
            'refund_fee_2' => trans('invoice.refund_fee_2'),
            'account_balance_2' => trans('invoice.account_balance_2'),
            'memo_2' => trans('invoice.memo_2'),
        ];
    }

    public function importData($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return array_values($this->headingMapping);
    }

    public function getHeaderTemplate()
    {
        $headerTemplate = [];

        foreach (array_keys($this->headingMapping) as $key) {
            $headerTemplate[$key] = '';
        }

        return $headerTemplate;
    }

    public function addHeadingAdmin()
    {
        $this->headingMapping['admin_note'] = trans('invoice.admin_note');
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'use_bom' => true,
            'output_encoding' => 'UTF8',
            'enclosure' => '',
        ];
    }
}


