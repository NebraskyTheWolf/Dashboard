<?php

declare(strict_types=1);

namespace Orchid\Platform\Models;

use App\Models\Security\Account\UserRestrictions;
use App\Orchid\Presenters\AuditPresenter;
use App\Orchid\Presenters\UserPresenters;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Orchid\Access\UserAccess;
use Orchid\Access\UserInterface;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Metrics\Chartable;
use Orchid\Screen\AsSource;
use Orchid\Support\Facades\Dashboard;
use Ramsey\Uuid\Uuid;

class User extends Authenticatable implements UserInterface
{
    use AsSource, Chartable, Filterable, HasFactory, Notifiable, UserAccess;

    protected $notification;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'permissions',
        'fcm_token',
        'is_fcm',
        'bio',
        'pronouns'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions'          => 'array',
        'email_verified_at'    => 'datetime',
        'is_fcm'               => 'boolean'
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id'         => Where::class,
        'name'       => Like::class,
        'email'      => Like::class,
        'updated_at' => WhereDateStartEnd::class,
        'created_at' => WhereDateStartEnd::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'updated_at',
        'created_at',
    ];

    /**
     * Create an admin user.
     *
     * @param string $name The name of the admin user.
     * @param string $email The email of the admin user.
     * @param string $password The password of the admin user.
     *
     * @return void
     * @throws \Exception If user with the same email already exists.
     *
     * @throws \Exception If CLI is restricted.
     */
    public static function createAdmin(string $name, string $email, string $password): void
    {
        if (env('CLI_RESTRICTED', false)) {
            throw new \Exception('CLI Restricted');
        }

        throw_if(static::where('email', $email)->exists(), 'User exists');

        static::create([
            'name'        => $name,
            'email'       => $email,
            'password'    => Hash::make($password),
            'permissions' => Dashboard::getAllowAllPermission(),
        ]);
    }


    /**
     * @return UserPresenters
     */
    public function presenter()
    {
        return new UserPresenters($this);
    }

    public function auditPresenter()
    {
        return new AuditPresenter($this);
    }

    /**
     * Check if the user is terminated.
     *
     * @return bool True if the user is terminated, false otherwise.
     */
    public function isTerminated(): bool
    {
        return UserRestrictions::where('user_id', $this->id)->exists();
    }

    /**
     * Terminate the user's account based on the provided actor.
     *
     * @param int $actor The actor responsible for terminating the user's account.
     *
     * @return void
     */
    public function terminate($actor)
    {
        if ($this->isTerminated()) {
            \app\Models\Security\Account\UserRestrictions::where('user_id', $this->id)->delete();
        } else {
            $termination = new UserRestrictions();
            $termination->user_id = $this->id;
            $termination->actor_id = $actor;
            $termination->save();
        }
    }

    /**
     * Create a user token.
     *
     * This method generates a unique token for the user and saves it in the database.
     * The generated token is returned as a string.
     *
     * @return string The generated user token.
     */
    public function createUserToken(): string
    {
        $token = new \app\Models\Security\Auth\UserApiToken();
        $token->user_id = $this->id;
        $token->token = base64_encode(Uuid::uuid4()->toString());
        $token->save();

        return $token->token;
    }

    /**
     * Checks if the target user has bigger power than the current user.
     *
     * @param User $targetUser The target user to compare powers with.
     *
     * @return bool Returns true if the target user has bigger power, false otherwise.
     */
    public function hasUserBiggerPower(User $targetUser): bool
    {
        if (!$this->exists)
            return false;

        if ($this->permissions === null
            || empty($this->permissions)
            || count($this->permissions) <= 0)
            return false;

        $userPermissions = count($this->permissions);
        $targetUserPermissions = count($targetUser->permissions);

        return $targetUserPermissions > $userPermissions;
    }


    /**
     * Update the Firebase Cloud Messaging (FCM) token for the user.
     *
     * @param string $token The new FCM token for the user.
     *
     * @return void
     */
    public function updateFCMToken(string $token): void
    {
        $this->update([
            'fcm_token' => $token,
            'is_fcm' => true
        ]);
    }

    /**
     * Sends a FCM (Firebase Cloud Messaging) notification to the user.
     *
     * @param string $title The title of the notification. Maximum 65 characters.
     * @param string $body The body of the notification. Maximum 240 characters.
     *
     * @return bool Returns true if the notification is sent successfully, otherwise false.
     * @throws Exception
     */
    public function sendFCMNotification(string $title, string $body): bool
    {
        if (!$this->isFCMAvailable()) {
            return false;
        }

        if (!$this->isTitleValid($title) || !$this->isBodyValid($body)) {
            return false;
        }

        return $this->sendNotification($title, $body);
    }

    /**
     * Check if FCM (Firebase Cloud Messaging) is available for the current user.
     *
     * @return bool Returns true if FCM is available, false otherwise.
     */
    private function isFCMAvailable(): bool
    {
        if (!$this->is_fcm) {
            return false;
        }
        return true;
    }

    /**
     * Check if the provided title is valid.
     *
     * @param string $title The title to be checked.
     *
     * @return bool Returns true if the title is valid, false otherwise.
     */
    private function isTitleValid(string $title): bool
    {
        return strlen($title) < self::MAX_TITLE_LENGTH;
    }

    /**
     * Checks if the body of a message is valid based on its length.
     *
     * @param string $body The body of the message.
     *
     * @return bool Returns true if the body is valid, and false otherwise.
     */
    private function isBodyValid(string $body): bool
    {
        return strlen($body) < self::MAX_BODY_LENGTH;
    }

    /**
     * Send a notification to the user through Firebase Cloud Messaging (FCM).
     *
     * @param string $title The title of the notification.
     * @param string $body The body/content of the notification.
     *
     * @return bool Whether the notification was successfully sent or not.
     * @throws Exception If there was an error while sending the FCM notification.
     */
    private function sendNotification(string $title, string $body): bool
    {
        $message = CloudMessage::fromArray([
            'token' => $this->fcm_token,
            'notification' => [
                'title' => $title,
                'body' => $body
            ],
        ]);

        try {
            Firebase::messaging()->send($message);
            return true;
        } catch (MessagingException|FirebaseException $e) {
            throw new Exception('Failed to send FCM notification: ' . $e->getMessage());
        }
    }

    const int MAX_TITLE_LENGTH = 65;
    const int MAX_BODY_LENGTH = 240;

    public function getLanguage(): string
    {
        return $this->language ?? 'en';
    }
}
