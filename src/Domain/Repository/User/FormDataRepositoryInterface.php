<?php

namespace Proximum\Vimeet\Domain\Repository\User;

use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\FormData;

interface FormDataRepositoryInterface
{
    public function add(FormData $formData): void;
    public function update(FormData $formData): void;
    public function save(FormData $formData): void;
    public function getByUserAndFormTemplate(User $user, FormTemplate $formTemplate): ?FormData;
    public function getDataByEventIdAndUserId(int $eventId, int $userId): array;
}
