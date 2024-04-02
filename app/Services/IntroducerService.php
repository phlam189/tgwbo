<?php

namespace App\Services;

use App\Repositories\Interfaces\IntroducerRepositoryInterface;

class IntroducerService
{
    public IntroducerRepositoryInterface $introducerRepository;

    public function __construct(IntroducerRepositoryInterface $introducerRepository)
    {
        $this->introducerRepository = $introducerRepository;
    }

    public function store($data)
    {
        return $this->introducerRepository->create($data);
    }

    public function update($id, $request)
    {
        return $this->introducerRepository->update($id, $request->all());
    }

    public function find($id)
    {
        return $this->introducerRepository->find($id);
    }

    public function getList()
    {
        return $this->introducerRepository->getList();
    }

    public function checkUniqueEmail($request)
    {
        return $this->introducerRepository->checkUniqueEmail($request);
    }

    public function showWithContractor($id){
        return $this->introducerRepository->showWithContractor($id);
    }
    public function showDetail($id){
        return $this->introducerRepository->showDetail($id);
    }
}
