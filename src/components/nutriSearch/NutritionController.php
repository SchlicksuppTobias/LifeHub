<?php

namespace Tobias\LifeHub\components\nutriSearch;

use Tobias\LifeHub\shared\interfaces\NutritionRepositoryInterface;

class NutritionController
{
    public function __construct(
        private NutritionRepositoryInterface $repository
    ) {}

    public function handle(string $search = ''): array
    {
        return $search !== ''
            ? $this->repository->search($search)
            : $this->repository->getAll();
    }

}