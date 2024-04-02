<?php

namespace App\Services;

use App\Models\InvoiceContructor;
use App\Repositories\Interfaces\ContractorRepositoryInterface;
use App\Repositories\Interfaces\InvoiceContructorRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class InvoiceContructorService
{
    public InvoiceContructorRepositoryInterface $invoiceContructorRepository;

    public ContractorRepositoryInterface $contractorRepository;

    const MAX_LENGTH_NUMBER = 15;

    public function __construct(
        InvoiceContructorRepositoryInterface $invoiceContructorRepository,
        ContractorRepositoryInterface $contractorRepository
    )
    {
        $this->invoiceContructorRepository = $invoiceContructorRepository;
        $this->contractorRepository = $contractorRepository;
    }

    public function store($data)
    {
        return $this->invoiceContructorRepository->create($data);
    }

    public function update($id, $request)
    {
        $isDuplicate = $this->checkInvoiceNumber($request['contructor_id'], $request['number']);
        if (!empty($isDuplicate)) {
            $this->invoiceContructorRepository->update($isDuplicate[0]['id'], $request->all());
        } else {
            $this->invoiceContructorRepository->create($request->all());
        }
        return $request->all();
    }

    public function find($id)
    {
        return $this->invoiceContructorRepository->find($id);
    }

    public function findByNumber($request)
    {
        return $this->invoiceContructorRepository->findByNumber($request);
    }

    public function getList($request, $invoiceContructorList)
    {
        [$result, $invoiceContractorIsHonsha] = [
            $this->invoiceContructorRepository->getList($request)?->toArray(),
            $this->contractorRepository->findContractorIsHonsha()?->toArray(),
        ];

        if (empty($result)) {
            return $result;
        }

        if ($request['generate_number'] == 1) {
            $invoiceContractor = InvoiceContructor::where('contructor_id', $request['contractor_id'])
                ->orderBy('created_at')
                ->first()
                ?->toArray();
            $invoiceContractorDesc = InvoiceContructor::where('contructor_id', $request['contractor_id'])
                ->orderBy('created_at', 'desc')
                ->first()
                ?->toArray();

            if (!empty($invoiceContractor)) {
                $parts = explode('-', $invoiceContractorDesc['number']);
                $firstPart = $parts[0];
                if (isset($parts[1])) {
                    $incrementedPart = preg_match('/^\d+$/', $parts[1])
                        ? str_pad(ltrim($parts[1], '0') + 1, strlen($parts[1]), '0', STR_PAD_LEFT)
                        : substr(Hash::make($parts[1]), -(self::MAX_LENGTH_NUMBER - strlen($firstPart.'-')));
                } else {
                    $incrementedPart = '001';
                }
                $result['number'] = "$firstPart-$incrementedPart";
            } else {
                $result['number'] = "{$result['invoice_prefix']}" . Carbon::now()->format('Ymd');
            }
        }

        return array_merge($result, [
            'company_email' => $invoiceContractorIsHonsha['email'] ?? '',
            'company_address' => $invoiceContractorIsHonsha['address'] ?? '',
            'company_name_honsha' => $invoiceContractorIsHonsha['company_name'] ?? '',
            'contact_name' => $invoiceContractorIsHonsha['manager'] ?? ''
        ]);
    }
    public function getListByContractorId($request)
    {
        $date = Carbon::parse($request->from_date);
        return $this->invoiceContructorRepository->getListByContractorId($request, $date);
    }

    public function checkInvoiceNumber($idContractor, $number)
    {
        return $this->invoiceContructorRepository->findBy([
            'contructor_id' => $idContractor,
            'number' => $number
        ]);
    }
}
