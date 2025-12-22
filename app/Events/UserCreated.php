<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие создания пользователя
 * 
 * Вызывается после успешного создания пользователя в системе
 */
class UserCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Созданный пользователь
     * 
     * @var User
     */
    public User $user;

    /**
     * Незахэшированный пароль (для синхронизации с Moodle)
     * 
     * @var string
     */
    public string $plainPassword;

    /**
     * Создать новый экземпляр события
     * 
     * @param User $user Созданный пользователь
     * @param string $plainPassword Незахэшированный пароль
     */
    public function __construct(User $user, string $plainPassword)
    {
        $this->user = $user;
        $this->plainPassword = $plainPassword;
    }
}

