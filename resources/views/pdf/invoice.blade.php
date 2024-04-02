<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>invoice </title>

    <link href="{{ storage_path('fonts/noto-sans-cjk-jp/css/font.css')}}" rel="stylesheet">

    <style type="text/css">
        #invoice-details tbody tr:nth-child(even) {
            background-color: #F5F8F9;
        }

        body {
            font-family: "Noto Sans Japanese", Helvetica, Arial, sans-serif;
        }

        @page { margin-top: 10px; margin-bottom: 10px}

        .area_right_total {
            font-weight: 400;
            font-size: 14px;
            line-height: 20px;
            color:#435564;
            text-align: right;
        }
        .page-break {
            page-break-before: always;
        }
        .box {
            margin-top: 0px;
            word-wrap: break-word;
        }
        .title-center{
            text-align: center;
        }
        .money {
            text-align: right;
        }

    </style>

</head>
<body>
    <div >

    </div>
    <table width="100%" style="padding-bottom: 10px; border-bottom: 10px solid #1C78DD;">
        <tr>
            <td style="color: #1C78DD; text-transform: uppercase; font-weight: 700; vertical-align: bottom">
                <div style="font-size: 32px; margin-bottom: 0">REPORT</div>
                <span style="font-size: 20px; margin-top: 0">{{$contrustor->company_name}}</span>
            </td>
            <td style="text-align: right; color: #1C78DD; font-weight: 400; font-size: 14px;" >
                <div style="max-width: 400px ;display: inline-block;">
                    {{$contrustor->address}} <br/>
                    {{$contrustor->email}} <br/>
                    {{$contrustor->manager}}
                </div>
            </td>
        </tr>
    </table>

    <table style="padding-top: 15px; width:100%; table-layout: auto;">
        <tr>
            <td>
                <span style="color: #1C78DD; text-transform: uppercase; font-weight: 700; font-size: 22px; ">
                    Bill to:
                </span>
            </td>
            <td></td>
        </tr>
    </table>

    <div>
        <div style="display: inline-block; padding-bottom: 27px;">
            <table style="clear: both" cellpadding="0">
                <tr width="100%">
                    <td style="width: 50%">
                        <span style="color: #06152B; font-weight: 700;font-size: 18px;">
                            {{$client->company_name}}
                        </span>
                    </td>

                </tr>
                <tr>
                    <td style="color: #06152B; font-weight: 400;font-size: 14px;">
                        <span>{{$client->address}}</span>
                    </td>

                </tr>
                <tr>
                    <td style="color: #06152B; font-weight: 400;font-size: 14px;">
                        <span>{{$client->email}}</span></td>
                </tr>
            </table>
        </div>
        <div style="float: right">
            <table width="100%" style="clear: both; font-size: 14px;" cellpadding="0">
                <tr width="100%">
                    <td align="right">
                        <span style="font-weight: 700; font-size: 14px; color: #06152B;">
                            Invoice Date:
                        </span>
                    </td>
                    <td style="color: #435564; font-weight: 400;  text-align:right; width: 130px">
                        {{\Carbon\Carbon::createFromFormat('Y-m-d', $invoice->invoice_date)->format('Y/m/d')}}
                    </td>
                </tr>
                <tr>
                    <td align="right"><span style="font-weight: 700; font-size: 14px; color: #06152B;">Due Date:</span></td>
                    <td style="color: #435564; text-align:right">{{\Carbon\Carbon::createFromFormat('Y-m-d', $invoice->due_date)->format('Y/m/d')}}</td>
                </tr>
                <tr>
                    <td align="right"><span style="font-weight: 700; font-size: 14px; color: #06152B;">Invoice No:</span></td>
                    <td style="color: #435564; font-weight: 400;  text-align:right">{{$invoice->invoice_no}}</td>
                </tr>
            </table>
        </div>
    </div>

    <table width="100%" style="border-top: 2px solid #1C78DD;">
        <tr>
            <td align="right" style="font-size: 14px; line-height: 20px">
                <span style="font-weight: 700; color: #06152B;">
                    Period:
                </span>
                <span style="color: #435564; font-weight: 400; ">{{\Carbon\Carbon::createFromFormat('Y-m-d', $invoice->period_from)->format('Y/m/d')}} ~ {{\Carbon\Carbon::createFromFormat('Y-m-d', $invoice->period_to)->format('m/d')}}</span>
            </td>
        </tr>
    </table>

    <table style="width:100%;" id="invoice-details" cellspacing="0" cellpadding="5">
        <thead style="background-color: #1C78DD; color: white; font-weight: 700; line-height: 20px;">
        <tr>
            <td height="50px" style='width: 16%;' class="title-center">DESCRIPTION</td>
            <td style="width: 14%"  class="title-center">RATE</td>
            <td style="width: 15%" class="title-center">QTY</td>
            <td style="width: 30%" class="title-center">UNIT PRICE</td>
            <td style="width: 25%" class="title-center">TOTAL</td>
        </tr>
        </thead>
        <tbody>
        @foreach ($dataPdf as $detail)
            @if ($loop->index == 12 or ($loop->index > 12 and $loop->index % 30 == 0))
                </tbody>
                </table>
                <p class="page-break"></p>
                <table width="100%" >
                    <tr>
                        <td align="right" style="font-size: 14px; line-height: 20px">
                            <span style="font-weight: 700; color: #06152B;">
                                Period:
                            </span>
                            <span style="color: #435564; font-weight: 400; ">{{\Carbon\Carbon::createFromFormat('Y-m-d', $invoice->period_from)->format('Y/m/d')}} ~ {{\Carbon\Carbon::createFromFormat('Y-m-d', $invoice->period_to)->format('m/d')}}</span>
                        </td>
                    </tr>
                </table>
                <table style="width:100%;" id="invoice-details" cellspacing="0" cellpadding="5">
                    <thead style="background-color: #1C78DD; color: white; font-weight: 700; line-height: 20px;">
                    <tr>
                        <td height="50px" style='width: 16%; padding-left: 5px'>DESCRIPTION</td>
                        <td style="width: 14%"  class="title-center">RATE</td>
                        <td style="width: 15%" class="title-center">QTY</td>
                        <td style="width: 30%" class="title-center">UNIT PRICE</td>
                        <td style="width: 25%" class="title-center">TOTAL</td>
                    </tr>
                    </thead>
                    <tbody>
            @endif
            <tr style="font-weight: 400; font-size: 14px; color: #788B9A; vertical-align: middle">
                <td height="40px" style='padding-left: 5px' >
                    {{ $detail['description'] }}
                </td>
                <td class="money">{{ $detail['rate'] }}</td>
                <td class="money">{{ $detail['qty'] }}</td>
                <td class="money">
                    {{ $detail['total_amount'] }}
                </td>
                <td class="money">
                    {{ $detail['system_usage_fee'] }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table style="margin-top: 5px; width: 100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="right" style="font-weight: 700;font-size: 16px;color:#06152B; width: 80%">
                SUBTOTAL:
            </td>
            <td class="money" style="width: 20%">
                ¥{{number_format($invoice->sub_total , 0, '.', ',')}}</td>
        </tr>
        <tr>
            <td align="right" style="font-weight: 700;font-size: 16px;color:#06152B">
                DISCOUNT:
            </td>
            <td class="money">
                ¥{{number_format($invoice->discount_amount , 0, '.', ',')}}</td>
        </tr>
        <tr>
            <td align="right" style="font-weight: 700;font-size: 16px;color:#06152B">SUBTOTAL
                LESS DISCOUNT:
            </td>
            <td class="money">
                ¥{{number_format($invoice->sub_total - $invoice->discount_amount , 0, '.', ',')}}</td>
        </tr>
        <tr>
            <td align="right" style="font-weight: 700;font-size: 16px;color:#06152B">TAX
                RATE:
            </td>
            <td class="money">
                {{ $invoiceTaxRate }}%
            </td>
        </tr>
        <tr>
            <td align="right" style="font-weight: 700;font-size: 16px;color:#06152B">TOTAL
                TAX:
            </td>
            <td class="money">
                ¥{{number_format(ceil($invoice->total_tax) , 0, '.', ',')}}
            </td>
        </tr>
    </table>

    <table style="width: 100%">
        <tr>
            <td align="right">
                <span align="right"
                      style="color: #1C78DD;font-weight: bold;font-size: 16px;line-height: 23px; text-align: right"
                      colspan="4">Balance Due:</span>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span align="right"
                      style="color: #1C78DD;font-weight: bold;font-size: 26px;line-height: 38px;">¥{{number_format($invoice->balance , 0, '.', ',')}}</span>
            </td>
        </tr>
    </table>

    <div style="margin-top: 10px; font-weight: bold; font-size: 16px; line-height: 23px;">Thank you for your business!</div>

    <div style="color: #06152B; font-weight: bold; margin-top: 15px">Terms & Instructions: </div>
    <div style="color: #06152B; font-weight: 550; width: 510px; overflow-wrap: anywhere;">
        <div class="box">{{ $invoice->memo }}</div>
    </div>

</body>
</html>
