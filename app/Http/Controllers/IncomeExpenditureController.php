<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\IncomeExpenditure;
use App\Models\IncomeExpenditureDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class IncomeExpenditureController extends Controller
{
    public function update(Request $request, $id)
    {
        $incomeData = Arr::except($request->all(), 'income_expenditure_details');
        $incomeDetailsData = $request->all()['income_expenditure_details'];
        $income = IncomeExpenditure::find($request->id);
        $incomeUpdate = $income->update($incomeData);

        foreach ($incomeDetailsData as $detailData) {
            if (isset($detailData['id'])) {
                $incomeDetail = IncomeExpenditureDetail::find($detailData['id']);
                if ($incomeDetail && $incomeDetail->income_expenditure_id == $income->id) {
                    $incomeDetail->update($detailData);
                }
            } else {
                $detailData['income_expenditure_id'] = $income->id;
                IncomeExpenditureDetail::create($detailData);
            }
        }


        return response()->json($income->load('incomeExpenditureDetails'));
    }

    public function exportPdf (Request $request) {
        $income = IncomeExpenditure::find($request->id);
        if (!$income) {
            return response()->json([
                'message' => 'Data not found'
            ]);
        }

        $clients = Client::withTrashed('client_id', 'represent_name')->get();
        $incomeDetailData = IncomeExpenditureDetail::where('income_expenditure_id', $income ? $income->id : 0)->get();

        foreach ($incomeDetailData as $row) {
            $client = $clients->where('client_id', $row->client_id);
            $row->represent_name = '';
            if ($client->isNotEmpty()) {
                $row->represent_name = $client->first()->represent_name;
            }
        }

        $incomeDetailDataType1 = $incomeDetailData->where('type', 1)->groupBy('client_id')->sortKeys();
        $incomeDetailDataType2 = $incomeDetailData->where('type', 2)->groupBy('client_id')->sortKeys();
        $incomeDetailDataType3 = $incomeDetailData->where('type', 3)->groupBy('client_id');
        $incomeDetailDataType4 = $incomeDetailData->where('type', 4);
        $incomeDataSum = collect([]);

        $rowAccountUsageFee = $this->getDataTypeFee($incomeDetailDataType4, 1);
        if ($rowAccountUsageFee) {
            $incomeDataSum->push($rowAccountUsageFee);
        }

        $rowAccountUsageFee = $this->getDataTypeFee($incomeDetailDataType4, 2);
        if ($rowAccountUsageFee) {
            $incomeDataSum->push($rowAccountUsageFee);
        }

        $rowAccountUsageFee = $this->getDataTypeFee($incomeDetailDataType4, 3);
        if ($rowAccountUsageFee) {
            $incomeDataSum->push($rowAccountUsageFee);
        }

        $rowAccountUsageFee = $this->getDataTypeByItemName($incomeDetailDataType4, 'クライアント紹介手数料');
        if ($rowAccountUsageFee) {
            $incomeDataSum->push($rowAccountUsageFee);
        }

        $rowAccountUsageFee = $this->getDataTypeByItemName($incomeDetailDataType4, '口座紹介手数料');
        if ($rowAccountUsageFee) {
            $incomeDataSum->push($rowAccountUsageFee);
        }

        $rowAccountUsageFee = $this->getDataTypeFee($incomeDetailDataType4, 6);
        if ($rowAccountUsageFee) {
            $incomeDataSum->push($rowAccountUsageFee);
        }

        $rowAccountUsageFee = $this->getDataTypeFee($incomeDetailDataType4, 7);
        if ($rowAccountUsageFee) {
            $incomeDataSum->push($rowAccountUsageFee);
        }

        $rowAccountUsageFee = $this->getDataTypeFee($incomeDetailDataType4, 8);
        if ($rowAccountUsageFee) {
            $incomeDataSum->push($rowAccountUsageFee);
        }

        $incomeDetailDataType1 = $this->sortLineTotalToTop($incomeDetailDataType1);
        $incomeDetailDataType2 = $this->sortLineTotalToTop($incomeDetailDataType2);

        $data = [
            'income' => $income,
            'income_detail_type1' => $incomeDetailDataType1,
            'income_detail_type2' => $incomeDetailDataType2,
            'income_detail_type3' => $incomeDetailDataType3->sortKeys(),
            'income_detail_type4' => $incomeDetailDataType4,
            'data_sum_table_4' => $incomeDataSum->groupBy('item_name'),
            'lang' => $request->lang ?? 'jp',
        ];
        $date = Carbon::now()->format('YmdHis');

        $fileName = 'income_expenditure_' . $date . '.pdf';

        $invoice_path = config('filesystems.invoice');

        $filePath = $invoice_path . '/' . $fileName;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.income_summary', $data)->setPaper('a4');

        $content = $pdf->download()->getOriginalContent();
        Storage::disk('public')->put($filePath, $content);

        $url = config('app.url') . '/download?filename=' . $fileName;

        return response()->json([
            'download_link' => $url
        ]);
    }

    public function sortLineTotalToTop($list)
    {
        $activeItem = 'TOTAL';
        foreach ($list as $collection) {
            $findId = 0;
            $isSort = false;
            foreach ($collection as $key => $row) {
                if ($row->item_name == $activeItem) {
                    $findId = $key;
                    $isSort = true;
                }
            }
            if ($isSort) {
                $item = $collection->slice($findId, 1);
                $collection->forget($findId)->toArray();
                $collection->prepend($item->first());
            }


        }
        return $list;
    }

    public function getDataTypeByItemName($data, $itemName)
    {
        if ($itemName == 'クライアント紹介手数料') {
            $classification = 'Client';
        } else {
            $classification = 'Account';
        }
        $dataByType = $data->where('item_name', $itemName);
        $memo = '';
        $rowAccountUsageFee = null;
        foreach ($dataByType as $item) {
            $memo .= ($item->memo != '') ? $item->memo . '; ' : '';
            $rowAccountUsageFee = (object)[
                'item_name' => 'Referral Fee',
                'classification' => $classification,
                'type_fee' => 4,
                'amount' => $dataByType->sum('amount'),
                'profit' => $dataByType->sum('profit'),
                'previous_month' => $dataByType->sum('previous_month'),
                'memo' => $memo
            ];
        }
        return $rowAccountUsageFee;
    }

    public function getDataTypeFee ($data, $type) {

        $dataByType = $data->where('type_fee' ,$type);
        $rowAccountUsageFee = null;
        if ($dataByType->isNotEmpty()) {
            $itemName = $dataByType->first()->item_name;
            $classification = $dataByType->first()->classification;
            if ($type == 1) {
                $itemName = 'Account usage fee';
                $classification = 'Deposit account';
            }

            if ($type == 2) {
                $itemName = 'Account usage fee';
                $classification = 'Withdrawal account';
            }

            if ($type == 3) {
                $itemName = 'Outsourcing fee';
                $classification = $dataByType->first()->classification;
            }

            $memo = '';
            foreach ($dataByType as $item) {
                $memo .= ($item->memo != '') ? $item->memo . '; ' : '';
                $rowAccountUsageFee = (object)[
                    'item_name' => $itemName,
                    'classification' => $classification,
                    'type_fee' => $type,
                    'amount' => $dataByType->sum('amount'),
                    'profit' => $dataByType->sum('profit'),
                    'previous_month' => $dataByType->sum('previous_month'),
                    'memo' => $memo
                ];
            }
        }
        return $rowAccountUsageFee;
    }

    public function destroy($id)
    {
        try {
            IncomeExpenditureDetail::find($id)->delete();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_030'),
                ]
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' =>  __('messages.EUA_031'),
                ]
            );
        }
    }
}
