<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Platform\Models\User;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Password;
use Orchid\Screen\Layouts\Rows;

class UserPasswordLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        /** @var User $user */
        $user = $this->query->get('user');

        $placeholder = $user->exists
            ? __('user.screen.user.password.placeholder.one')
            : __('user.screen.user.password.placeholder.two');

        return [
            Password::make('user.password')
                ->placeholder($placeholder)
                ->title(__('user.screen.user.password.title')),
        ];
    }
}
