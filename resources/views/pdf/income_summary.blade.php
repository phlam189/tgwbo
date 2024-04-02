<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        {{
            trans(
                'income_pdf.title',
                [$lang === 'en'
                ? \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $income->from_date)->format('F Y')
                : \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $income->from_date)->isoFormat('YYYY年M月')],
                $lang
            )
        }}
    </title>
    <link href="{{ storage_path('fonts/noto-sans-cjk-jp/css/font.css')}}" rel="stylesheet">
    <style type="text/css">
        .table-custom tbody tr:nth-child(even) {
            background-color: #ecf0f1;
        }

        .th-custom,
        .td-custom {
            height: 10px;
            /*padding-left: 10px;*/
            font-size: 8px;
        }

        .td-custom {
            /*line-height: 20px;*/
            color: #788b9a;
        }

        body {
            font-family: "Noto Sans Japanese", Helvetica, Arial, sans-serif;
        }

        .page {
            width: 100%;
        }

        .table-custom {
            width: 100%;
        }

        .th-custom {
            text-align: left;
        }

        .td-custom {
            text-align: left;
        }

        .table-custom th {
            border-top: 1px solid #dddddd;
            border-bottom: 2px solid black;
            text-align: left;
            padding: 0 8px 0 8px;
        }

        .table-custom td {
            border-top: 1px solid #dddddd;
            border-bottom: 1px solid #dddddd;
            text-align: left;
            padding: 0 8px 0 8px;
        }

        .table-custom th {
            border-right: 1px solid #dddddd;
        }

        .table-custom th:last-child {
            border-right: unset;
        }

        .table-custom td {
            border-right: 1px solid #dddddd;
        }

        .table-custom td:last-child {
            border-right: unset;
        }

        .ml-5 {
            margin-right: 5px;
        }
    </style>

</head>

<body>
<table width="100%">
    <tr>
        <td style="text-align: center">
            <div class="my-japanese-text" style="font-size: 10px; font-weight: 700; text-align: left">
                {{
                    trans(
                        'income_pdf.title',
                        [$lang === 'en'
                        ? \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $income->from_date)->format('F Y')
                        : \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $income->from_date)->isoFormat('YYYY年M月')],
                        $lang
                    )
                }}
            </div>
        </td>
        <td style="text-align: right; font-size: 10px;">
            <div style="max-width: 305px; display: inline-block">
                <div>
                    <span class="ml-5" style="color: #788b9a; font-weight: 700">
                        {{ trans('income_pdf.duration', [], $lang) }}:
                    </span>
                    <strong>{{ \Illuminate\Support\Carbon::parse($income->from_date)->format('Y/m/d') . ' - ' .\Illuminate\Support\Carbon::parse($income->to_date)->format('Y/m/d') }}</strong> <br />
                </div>
                <div style="padding-right: 59px">
                    <span style="color: #788b9a; font-weight: 700;" class="ml-5">
                        {{ trans('income_pdf.total_balance', [], $lang) }}:
                    </span>
                    <strong>¥{{ number_format(ceil($income->total_balance)) }}</strong> <br />
                </div>
                <div style="padding-right: 59px">
                    <span style="color: #788b9a; font-weight: 700;" class="ml-5">
                        {{ trans('income_pdf.total_spending', [], $lang)}}:
                    </span>
                    <strong>¥{{ number_format(ceil($income->total_spending)) }}</strong> <br />
                </div>
                <div style="padding-right: 56.2px">
                    <span style="color: #788b9a; font-weight: 700;" class="ml-5">
                        {{ trans('income_pdf.profit_and_loss', [], $lang) }}:
                    </span>
                    <strong>¥{{ number_format($income->profit) }}</strong> <br />
                </div>
                @if($income->profit_include_wm <> 0)
                    <div style="padding-right: 56.5px">
                    <span style="color: #788b9a; font-weight: 700;" class="ml-5">
                        {{ trans('income_pdf.profit_and_loss_wm', [], $lang) }}:
                    </span>
                        <strong>¥{{number_format($income->profit_include_wm) }}</strong>
                    </div>
                @endif
            </div>
        </td>
    </tr>
</table>
<div class="page">
    <table style="width:100%; table-layout: auto">
        <tr>
            <td><span style="font-weight: 700; font-size: 10px;">{{ trans('income_pdf.department_income_title', [], $lang) }}</span></td>
            <td></td>
        </tr>
    </table>
    <div style="font-weight: 700; font-size: 10px; margin-bottom: 10px;">
        1.{{ trans('income_pdf.deposit', [], $lang) }}
    </div>
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 50px;"
           id="deposit-income" class="table-custom">
        <thead style="font-weight: 700;">
        <tr>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.client_name', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.classification', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.number_of_deposits', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.deposit_amount', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.contract_interest_rate', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.earnings', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.last_month', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.memo', [], $lang) }}</th>
        </tr>
        </thead>
        <tbody>
        @php
            $totalNumber = 0;
            $totalAmount = 0;
            $totalProfit = 0;
            $totalLastMonth = 0;
        @endphp
        @forelse ($income_detail_type1 as $clientId => $data)
            @if($clientId)
                @foreach($data as $key => $row)
                    @php
                        $words = mb_str_split(\Illuminate\Support\Str::limit($row->memo, 50));
                        $slicedWords = [];

                        while (count($words) > 0) {
                        $slice = array_splice($words, 0, 5);
                        $slicedWords[] = implode(" ", $slice);
                        }
                        $row->memo = implode("</br>", $slicedWords);
                    @endphp
                <tr>
                    @if($key == 0)
                    @php
                        $totalNumber += $row->number_transaction;
                        $totalAmount += $row->amount;
                    @endphp
                    @endif
                    @php
                        $totalProfit += $row->profit;
                        $totalLastMonth += $row->previous_month;
                    @endphp
                    <td
                        class="td-custom"
                        @if($key != 0)
                            style="text-align: left; background-color: white; border-top: unset;border-bottom: unset"
                        @else
                            style="text-align: left; background-color: white; border-bottom: unset"
                        @endif
                    >
                        @if($key == 0)
                            {{ $row->represent_name }}
                        @endif
                    </td>
                        <td style="text-align: left" class="td-custom">{{$row->item_name}}</td>
                        <td style="text-align: right" class="td-custom">
                            {{ ($key == 0) ? number_format ($row->number_transaction) : '' }}
                        </td>
                        <td style="text-align: right" class="td-custom">¥{{ number_format($row->amount) }}</td>
                        <td style="text-align: right"
                            class="td-custom">{{ ($row->rate > 0) ? floor($row->rate * 100)/100 . '%' : (($row->item_name == 'TOTAL') ? '-' : '0%') }}</td>
                        <td style="text-align: right"
                            class="td-custom">{{ ($row->profit <> 0) ? '¥'.number_format($row->profit) : (($row->item_name == 'TOTAL') ? '-' : '¥0') }}</td>
                        <td style="text-align: right"
                            class="td-custom">{{ ($row->previous_month <> 0) ? '¥'.number_format($row->previous_month) : (($row->item_name == 'TOTAL') ? '-' : '¥0') }}</td>
                        <td style="text-align: left" class="td-custom">{!! $row->memo !!}</td>
                </tr>
                @endforeach
            @endif
        @empty
            <tr style="background-color: #d2e4f8;">
                <td class="td-custom" colspan="8"
                    style="text-align: center">{{ trans('income_pdf.empty', [], $lang) }}</td>
            </tr>
        @endforelse

        @foreach($income_detail_type1 as $clientId => $data)
            @if(!$clientId)
                @foreach($data as $key => $row)
                <tr style="background-color: #d2e4f8;">
                    @php
                        $totalNumber += $row->number_transaction;
                        $totalAmount += $row->amount;
                        $totalProfit += $row->profit;
                        $totalLastMonth += $row->previous_month;
                    @endphp
                    <td style="text-align: center" class="td-custom" rowspan="{{ $data->count() }}"
                        style="background-color: #d2e4f8; border-right: unset">
                        {{ trans('income_pdf.adjustment', [], $lang) }}
                    </td>
                    <td style="text-align: left" class="td-custom">{{$row->item_name}}</td>
                    <td style="text-align: right" class="td-custom">
                        {{ ($key == 0) ? number_format ($row->number_transaction) : '-' }}
                    </td>
                    <td style="text-align: right" class="td-custom">¥{{ number_format($row->amount) }}</td>
                    <td style="text-align: right"
                        class="td-custom">{{ ($row->rate > 0) ? floor($row->rate * 100)/100 . '%' : '-' }}</td>
                    <td style="text-align: right"
                        class="td-custom">{{ ($row->profit <> 0) ? '¥'.number_format($row->profit) : '-' }}</td>
                    <td style="text-align: right"
                        class="td-custom">{{ ($row->previous_month <> 0) ? '¥'.number_format($row->previous_month) : '-' }}</td>
                    <td style="text-align: left" class="td-custom">{{$row->memo}}</td>
                </tr>
                @endforeach
            @endif
        @endforeach

        <tr style="background-color: #1c78dd;">
            <td style="text-align: left" class="td-custom" style="font-weight: 700; color: white; border-right: unset"
                colspan="2">{{ trans('income_pdf.total', [], $lang) }}</td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: right">
                {{ number_format($totalNumber) }}
            </td>
            <td class="td-custom" style="color: white; border-right: unset; text-align: right">
                ¥{{ number_format($totalAmount) }}
            </td>
            <td class="td-custom" style="border-right: unset"></td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: right">
                ¥{{ number_format($totalProfit) }}
            </td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: right">
                ¥{{ number_format($totalLastMonth) }}
            </td>
            <td class="td-custom"></td>
        </tr>
        </tbody>
    </table>
    <div style="font-weight: 700; font-size: 10px; margin-bottom: 10px;">2.{{ trans('income_pdf.withdrawal', [],$lang) }}</div>
    <table width="100%" class="table-custom" cellspacing="0" cellpadding="0" style="margin-bottom: 50px;">
        <thead style="font-weight: 700;">
        <tr>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.client_name', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.classification', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.number_of_withdrawals', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.withdrawal_amount', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.contract_interest_rate', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.earnings', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.last_month', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.memo', [], $lang) }}</th>
        </tr>
        </thead>
        <tbody>
        @php
        $totalNumber = 0;
        $totalAmount = 0;
        $totalProfit = 0;
        $totalLastMonth = 0;
        @endphp

        @forelse ($income_detail_type2 as $clientId => $data)
            @if($clientId)
                @foreach($data as $key => $row)
                    @php
                        $words = mb_str_split(\Illuminate\Support\Str::limit($row->memo, 50));
                        $slicedWords = [];

                        while (count($words) > 0) {
                        $slice = array_splice($words, 0, 5);
                        $slicedWords[] = implode(" ", $slice);
                        }
                        $row->memo = implode("</br>", $slicedWords);
                    @endphp
                    <tr>
                        @if($key == 0)
                            @php
                                $totalNumber += $row->number_transaction;
                                $totalAmount += $row->amount;
                            @endphp
                        @endif
                        @php
                            $totalProfit += $row->profit;
                            $totalLastMonth += $row->previous_month;
                        @endphp
                        <td
                            class="td-custom"
                            @if($key != 0)
                                style="text-align: left; background-color: white; border-top: unset;border-bottom: unset"
                            @else
                                style="text-align: left; background-color: white; border-bottom: unset"
                            @endif
                        >
                            @if($key == 0)
                                {{ $row->represent_name }}
                            @endif
                        </td>
                            <td style="text-align: left" class="td-custom">{{$row->item_name}}</td>
                            <td style="text-align: right" class="td-custom">
                                {{ ($key == 0) ? number_format ($row->number_transaction) : '' }}
                            </td>
                            <td style="text-align: right" class="td-custom">¥{{ number_format($row->amount) }}</td>
                            <td style="text-align: right"
                                class="td-custom">{{ ($row->rate > 0) ? floor($row->rate * 100)/100 . '%' : (($row->item_name == 'TOTAL') ? '-' : '0%') }}</td>
                            <td style="text-align: right"
                                class="td-custom">{{ ($row->profit <> 0) ? '¥'.number_format($row->profit) : (($row->item_name == 'TOTAL') ? '-' : '¥0') }}</td>
                            <td style="text-align: right"
                                class="td-custom">{{ ($row->previous_month <> 0) ? '¥'.number_format($row->previous_month) : (($row->item_name == 'TOTAL') ? '-' : '¥0') }}</td>
                            <td style="text-align: left" class="td-custom">{!! $row->memo !!}</td>
                    </tr>
                @endforeach
            @endif
        @empty
            <tr style="background-color: #d2e4f8;">
                <td class="td-custom" colspan="8"
                    style="text-align: center">{{ trans('income_pdf.empty', [], $lang) }}</td>
            </tr>
        @endforelse

        @foreach($income_detail_type2 as $clientId => $data)
            @if(!$clientId)
                @foreach($data as $key => $row)
                    @php
                        $words = mb_str_split(\Illuminate\Support\Str::limit($row->memo, 50));
                        $slicedWords = [];

                        while (count($words) > 0) {
                        $slice = array_splice($words, 0, 5);
                        $slicedWords[] = implode(" ", $slice);
                        }
                        $row->memo = implode("</br>", $slicedWords);
                    @endphp
                    <tr style="background-color: #d2e4f8;">
                        @if($key == 0)
                            @php
                                $totalNumber += $row->number_transaction;
                                $totalAmount += $row->amount;
                            @endphp
                        @endif
                        @php
                            $totalProfit += $row->profit;
                            $totalLastMonth += $row->previous_month;
                        @endphp
                        <td style="text-align: left" class="td-custom" rowspan="{{ $data->count() }}"
                            style="background-color: #d2e4f8; border-right: unset">
                            {{ trans('income_pdf.adjustment', [], $lang) }}
                        </td>
                        <td style="text-align: left" class="td-custom">{{$row->item_name}}</td>
                        <td style="text-align: right" class="td-custom">
                            {{ ($key == 0) ? number_format ($row->number_transaction) : '-' }}
                        </td>
                        <td style="text-align: right" class="td-custom">¥{{ number_format($row->amount) }}</td>
                        <td style="text-align: right"
                            class="td-custom">{{ ($row->rate > 0) ? floor($row->rate * 100)/100 . '%' : '-' }}</td>
                        <td style="text-align: right"
                            class="td-custom">{{ ($row->profit <> 0) ? '¥'.number_format($row->profit) : '-' }}</td>
                        <td style="text-align: right"
                            class="td-custom">{{ ($row->previous_month <> 0) ? '¥'.number_format($row->previous_month) : '-' }}</td>
                        <td style="text-align: left" class="td-custom">{!! $row->memo !!}</td>
                    </tr>
                @endforeach
            @endif
        @endforeach

        <tr style="background-color: #1c78dd;">
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: left"
                colspan="2">
                {{ trans('income_pdf.total', [], $lang) }}
            </td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: right">
                {{number_format($totalNumber) }}
            </td>
            <td class="td-custom" style="color: white; border-right: unset; text-align: right">
                ¥{{ number_format($totalAmount) }}
            </td>
            <td class="td-custom" style="border-right: unset"></td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: right">
                ¥{{number_format($totalProfit) }}
            </td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: right">
                ¥{{number_format($totalLastMonth) }}
            </td>
            <td class="td-custom"></td>
        </tr>
        </tbody>
    </table>
    <div style="font-weight: 700; font-size: 10px; margin-bottom: 10px;">3.{{ trans('income_pdf.miscellaneous_income',[], $lang) }}</div>
    <table width="100%" class="table-custom" cellspacing="0" cellpadding="0" style="margin-bottom: 50px;">
        <thead style="font-weight: 700;">
        <tr>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.client_name', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.classification', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.number', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.amount_money', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.contract_interest_rate', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.earnings', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.last_month', [], $lang) }}</th>
            <th class="th-custom" style="text-align: center">{{ trans('income_pdf.memo', [], $lang) }}</th>
        </tr>
        </thead>
        <tbody>
        @php
            $totalNumber = 0;
            $totalAmount = 0;
            $totalProfit = 0;
            $totalLastMonth = 0;
        @endphp

        @forelse($income_detail_type3 as $clientId => $data)
            @foreach($data as $key => $row)
                @php
                    $words = mb_str_split(\Illuminate\Support\Str::limit($row->memo, 50));
                    $slicedWords = [];

                    while (count($words) > 0) {
                    $slice = array_splice($words, 0, 5);
                    $slicedWords[] = implode(" ", $slice);
                    }
                    $row->memo = implode("</br>", $slicedWords);
                    $totalNumber += $row->number_transaction;
                    $totalAmount += $row->amount;
                    $totalProfit += $row->profit;
                    $totalLastMonth += $row->previous_month;
                @endphp
                <tr>
                    <td
                        class="td-custom"
                        @if($key != 0)
                            style="text-align: left; background-color: white; border-top: unset;border-bottom: unset"
                        @else
                            style="text-align: left; background-color: white; border-bottom: unset"
                        @endif
                    >
                        @if($key == 0)
                            {{ $row->represent_name }}
                        @endif
                    </td>

                    <td style="text-align: left;" class="td-custom">
                        {{
                            in_array($row->item_name, [
                                'Interest',
                                'Settlement',
                                'Drawer',
                                'Refund',
                                'Deposit charge',
                                'Repayment',
                                'Transfer of funds',
                                'MISC',
                                'Deposits and withdrawals',
                                '利息',
                                '決済',
                                '引き出し',
                                '返金',
                                '他チャージ',
                                '預け入れ',
                                '資金移動',
                                '入出金'])
                                ? trans('income_pdf.'.strtolower(str_replace(" ", "_", trim($row->item_name))), [], $lang)
                                : $row->item_name
                            }}
                    </td>
                    <td style="text-align: right" class="td-custom">{{ number_format($row->number_transaction) }}</td>
                    <td style="text-align: right" class="td-custom">¥{{ number_format($row->amount) }}</td>
                    <td style="text-align: right"
                        class="td-custom">{{ ($row->rate > 0) ? floor($row->rate * 100)/100 . '%' : '-' }}</td>
                    <td style="text-align: right"
                        class="td-custom">{{ ($row->profit <> 0) ? '¥'.number_format(ceil($row->profit)) : '-' }}</td>
                    <td style="text-align: right"
                        class="td-custom">{{ ($row->previous_month <> 0) ? '¥'.number_format($row->previous_month) : '-' }}</td>
                    <td style="text-align: left" class="td-custom">{!! $row->memo !!}</td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td class="td-custom" colspan="8"
                    style="text-align: center">{{ trans('income_pdf.empty', [], $lang) }}</td>
            </tr>
        @endforelse
        <tr style="background-color: #1c78dd;">
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset;text-align: left"
                colspan="2">
                {{ trans('income_pdf.total', [], $lang) }}
            </td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: right">
                {{number_format($totalNumber) }}
            </td>
            <td class="td-custom" style="color: white; border-right: unset; text-align: right">
                ¥{{ number_format($totalAmount) }}
            </td>
            <td class="td-custom" style="border-right: unset"></td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: right">
                ¥{{ number_format($totalProfit) }}
            </td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: right">
                ¥{{ number_format($totalLastMonth) }}
            </td>
            <td class="td-custom"></td>
        </tr>
        </tbody>
    </table>
    <table style="width:100%; table-layout: auto; width: 100%">
        <tr>
            <td><span style="font-weight: 700; font-size: 10px;">{{ trans('income_pdf.department_of_expenditure', [],$lang) }}</span></td>
            <td></td>
        </tr>
    </table>
    <div style="font-weight: 700; font-size: 10px; margin-bottom: 10px;">4.{{ trans('income_pdf.expenses', [], $lang) }}</div>
    <table width="100%" class="table-custom" cellspacing="0" cellpadding="0" style="margin-bottom: 50px;">
        <thead style="font-weight: 700;">
        <tr>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.items', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.classification', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.number', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.unit', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.contract_interest_rate', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.amount_money', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.last_month', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.memo', [], $lang) }}</th>
        </tr>
        </thead>
        <tbody>
        @php
            $totalNumber = 0;
            $totalAmount = 0;
            $totalProfit = 0;
            $totalLastMonth = 0;
        @endphp
        @forelse (array_values($data_sum_table_4->toArray()) as $index => $items)
            @foreach($items as $key => $data)
                @php
                    $totalProfit += $data->profit;
                    $totalLastMonth += $data->previous_month;
                    $classification = $data->classification;

                    if ($data->type_fee == 1) {
                        $classification = trans('income_pdf.deposit_account', [], $lang);
                    }
                    if ($data->type_fee == 2) {
                        $classification = trans('income_pdf.withdrawal_account', [], $lang);
                    }
                    if (in_array($data->type_fee, [4, 5, 7, 8])) {
                        $classification = trans('income_pdf.'.str_replace(' ', '_', strtolower($data->classification)), [], $lang);
                    }

                    $explodeMemo = explode('; ', $data->memo);
                    $implodeMemo = [];
                    $memo = '';
                    foreach ($explodeMemo as $item) {
                        $slicedWords = [];
                        if ($item) {
                            $words = mb_str_split(\Illuminate\Support\Str::limit($item, 50));
                            while (count($words) > 0) {
                                $slice = array_splice($words, 0, 5);
                                $slicedWords[] = implode(" ", $slice);
                            }
                            $implodeMemo[] = implode("</br>", $slicedWords);
                        }
                    }
                    foreach ($implodeMemo as $item) {
                        $memo .= "- $item <br/>";
                    }
                    $data->memo = $memo;
                @endphp
                <tr>
                    <td
                        class="td-custom"
                        @if($key != 0)
                            style="text-align: left; background-color: white; border-top: unset; border-bottom: unset"
                        @else
                            style="text-align: left; background-color: white; border-bottom: unset"
                        @endif
                    >
                        @if($key == 0)
                            {{ trans('income_pdf.'.str_replace(' ', '_', strtolower($data->item_name)), [], $lang) }}
                        @endif
                    </td>
                    <td style="text-align: left" class="td-custom">{{$classification}}</td>
                    <td style="text-align: right" class="td-custom">-</td>
                    <td style="text-align: right" class="td-custom">-</td>
                    <td style="text-align: right" class="td-custom">-</td>
                    <td style="text-align: right" class="td-custom">
                        {{ ($data->profit <> 0) ? '¥'.number_format($data->profit) : '-' }}
                    </td>
                    <td style="text-align: right" class="td-custom">{{ ($data->previous_month <> 0) ? '¥'.number_format($data->previous_month) : '-' }}</td>
                    <td style="text-align: left" class="td-custom">{!! $data->memo !!}</td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td class="td-custom" colspan="8" style="text-align: center">{{ trans('income_pdf.empty', [], $lang) }}</td>
            </tr>
        @endforelse
        <tr style="background-color: #1c78dd;">
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: left"
                colspan="2">
                {{ trans('income_pdf.total', [], $lang) }}
            </td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset"></td>
            <td class="td-custom" style="color: white; border-right: unset"></td>
            <td class="td-custom" style="border-right: unset"></td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: right">
                ¥{{ number_format($totalProfit) }}
            </td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: right">
                ¥{{number_format($totalLastMonth) }}
            </td>
            <td class="td-custom"></td>
        </tr>
        </tbody>
    </table>
    <div style="font-weight: 700; font-size: 10px; margin-bottom: 10px;">5.{{ trans('income_pdf.breakdown_of_expenses', [], $lang) }}</div>
    <table width="100%" class="table-custom" cellspacing="0" cellpadding="0">
        <thead style="font-weight: 700;">
        <tr>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.items', [], $lang)
            }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.classification', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.number', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.unit', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.contract_interest_rate', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.payment_fees', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.last_month', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.payee_note', [], $lang) }}</th>
            <th style="text-align: center" class="th-custom">{{ trans('income_pdf.payee_status', [], $lang) }}</th>
        </tr>
        </thead>
        <tbody>
        @php
            $totalNumber = 0;
            $totalAmount = 0;
            $totalProfit = 0;
            $totalLastMonth = 0;
        @endphp
        @forelse ($income_detail_type4  as $data)
            <tr>
                @php
                    $totalNumber += $data->number_transaction;
                    $totalAmount += $data->amount;
                    $totalProfit += $data->profit;
                    $totalLastMonth += $data->previous_month;
                    $classification = $data->classification;
                    $itemName = ($data->item_name == 'Account usage fee')
                        ? trans('income_pdf.'.str_replace(' ', '_', strtolower($data->item_name)), [], $lang)
                        : $data->item_name;

                    if (in_array($data->type_fee, ['1', '2', '6', '3'])) {
                        if ($data->type_fee == 1) {
                            $classification = trans('income_pdf.deposit_account', [], $lang);
                        }
                        if ($data->type_fee == 2) {
                            $classification = trans('income_pdf.withdrawal_account', [], $lang);
                        }
                        if ($data->type_fee == 6 || $data->type_fee == 3) {
                            $itemName = trans('income_pdf.'.str_replace(' ', '_', strtolower($data->item_name)), [], $lang);
                        }
                    } else {
                        $itemName = trans('income_pdf.'.str_replace(' ', '_', strtolower($data->item_name)), [], $lang);
                        if (in_array($data->type_fee, ['4', '5'])) {

                            if ($data->represent_name) {
                                $itemName .= '(' .$data->represent_name . ')';
                            }
                            $classification = trans('income_pdf.' . ($data->type_fee == 4 ? 'deposit' : 'withdrawal'), [], $lang);
                        } elseif (in_array($data->type_fee, ['7', '8'])) {
                            $classification = trans('income_pdf.' . ($data->type_fee == 7 ? 'deposit_account' : 'withdrawal_account'), [], $lang);
                        }
                    }

                    $words = mb_str_split(\Illuminate\Support\Str::limit($data->memo, 50));
                    $slicedWords = [];

                    while (count($words) > 0) {
                      $slice = array_splice($words, 0, 5);
                      $slicedWords[] = implode(" ", $slice);
                    }
                    $data->memo = implode("</br>", $slicedWords);

                @endphp
                <td style="text-align: left" class="td-custom">{{$itemName}}</td>
                <td style="text-align: left" class="td-custom">{{$classification}}</td>
                <td style="text-align: right" class="td-custom">{{ number_format($data->number_transaction) }}</td>
                <td style="text-align: right" class="td-custom">¥{{ number_format($data->amount) }}</td>
                <td style="text-align: right"
                    class="td-custom">{{ ($data->rate > 0) ? floor($data->rate * 100)/100 . '%' : '-' }}</td>
                <td style="text-align: right"
                    class="td-custom">{{ ($data->profit <> 0) ? '¥'.number_format($data->profit) : '-' }}</td>
                <td style="text-align: right"
                    class="td-custom">{{ ($data->previous_month <> 0) ? '¥'.number_format($data->previous_month) : '-' }}</td>
                <td style="text-align: left; width: 10px;" class="td-custom">
                    {!! $data->memo !!}
                </td>
                <td style="text-align: left"
                    class="td-custom">{{\Illuminate\Support\Carbon::parse($data->payment_status)->year > 1 ? \Illuminate\Support\Carbon::parse($data->payment_status)->format('Y-m-d') : ''}}</td>
            </tr>
        @empty
            <tr>
                <td class="td-custom" colspan="9"
                    style="text-align: center">{{ trans('income_pdf.empty', [], $lang) }}</td>
            </tr>
        @endforelse
        <tr style="background-color: #1c78dd;">
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset;
                text-align: left" colspan="2">
                {{ trans('income_pdf.total', [], $lang) }}
            </td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset"></td>
            <td class="td-custom" style="color: white; border-right: unset"></td>
            <td class="td-custom" style="border-right: unset"></td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset;
                text-align: right">
                ¥{{ number_format($totalProfit) }}
            </td>
            <td class="td-custom" style="font-weight: 700; color: white; border-right: unset; text-align: right">
                ¥{{ number_format($totalLastMonth) }}
            </td>
            <td class="td-custom" style="border-right: unset;"></td>
            <td class="td-custom"></td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
