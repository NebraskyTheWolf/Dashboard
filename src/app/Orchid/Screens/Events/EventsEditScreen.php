<?php

namespace App\Orchid\Screens\Events;

use App\Events\UpdateAudit;
use App\Mail\ScheduleMail;
use App\Models\Events;
use App\Models\PlatformAttachments;
use Httpful\Exception\ConnectionErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Orchid\Platform\Models\User;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Map;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Ramsey\Uuid\Uuid;

class EventsEditScreen extends Screen
{

    public $events;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     * @throws ConnectionErrorException
     */
    public function query(Events $events): iterable
    {
        return [
            'events' => $events,
            'metrics' => [
                'summary' => [
                    'key' => 'summary',
                    'value' => $this->getSummary(),
                    'icon' => $this->getSummaryIcon()
                ],
                'temperature' => [
                    'key' => 'temperature',
                    'value' => $this->getTemperature() . '°C',
                    'icon' => 'bs.thermometer-half'
                ],
                'wind' => [
                    'key' => 'wind',
                    'value' => $this->getWindIndex() . '%',
                    'icon' => $this->getWindIcon()
                ],
                'precipitation' => [
                    'key' => 'precipitation',
                    'value' => $this->getPrecipitation() . '%',
                    'icon' => 'bs.cloud-drizzle'
                ],
            ]
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->events->exists ? __('events.screen.edit.title') : __('events.screen.edit.title.create');
    }

    public function permission(): iterable
    {
        return [
            'platform.systems.events.write'
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(__('events.screen.edit.button.create'))
                ->icon('bs.pencil')
                ->method('createOrUpdate')
                ->canSee(!$this->events->exists),

            Button::make(__('events.screen.edit.button.update'))
                ->icon('bs.note')
                ->method('createOrUpdate')
                ->canSee($this->events->exists)
                ->canSee($this->events->status == "INCOMING")
                ->canSee($this->events->status == "STARTED"),

            Button::make(__('events.screen.edit.button.cancel'))
                ->icon('bs.trash')
                ->confirm(__('common.modal.event.cancel'))
                ->method('cancel')
                ->canSee($this->events->exists)
                ->canSee($this->events->status == "INCOMING")
                ->canSee($this->events->status == "STARTED")
                ->type(Color::DANGER),

            Button::make(__('events.screen.edit.button.undo'))
                ->icon('bs.arrow-clockwise')
                ->method('undo')
                ->canSee($this->events->exists)
                ->canSee($this->events->status == "ENDED")
                ->type(Color::INFO),

            Button::make(__('events.screen.edit.button.finish'))
                ->icon('bs.check-lg')
                ->method('finish')
                ->canSee($this->events->exists)
                ->canSee($this->events->status == "INCOMING")
                ->canSee($this->events->status == "STARTED")
                ->type(Color::SUCCESS),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        if ($this->events->event_id == NULL) {
            $this->events->event_id = Uuid::uuid4();
        }

        return [
            Layout::tabs([
                'Information' => [
                    Group::make([
                        Input::make('events.name')
                            ->title(__('events.screen.input.event_name.title'))
                            ->placeholder(__('events.screen.input.event_name.placeholder'))
                            ->help(__('events.screen.input.event_name.help'))
                            ->disabled($this->events->status == "CANCELLED"),

                        Quill::make('events.descriptions')
                            ->title(__('events.screen.input.description.title'))
                            ->canSee(!$this->events->exists || ($this->events->exists && $this->events->status == "INCOMING")),

                        Select::make('events.type')
                            ->options([
                                'PHYSICAL'   => __('events.screen.input.type.choice.one'),
                                'ONLINE' => __('events.screen.input.type.choice.two'),
                            ])
                            ->title(__('events.screen.input.type.title'))
                            ->help(__('events.screen.input.type.help'))
                            ->disabled($this->events->status == "CANCELLED")
                    ]),

                    Group::make([
                        DateTimer::make('events.begin')
                            ->title(__('events.screen.input.begin.title'))
                            ->disabled($this->events->status == "CANCELLED"),

                        DateTimer::make('events.end')
                            ->title(__('events.screen.input.end.title'))
                            ->disabled($this->events->status == "CANCELLED"),
                    ]),

                    Group::make([
                        Map::make('events.min')
                            ->title(__('events.screen.input.map_min.title'))
                            ->help(__('events.screen.input.map_min.help')),
                        Map::make('events.max')
                            ->title(__('events.screen.input.map_max.title'))
                            ->help(__('events.screen.input.map_max.help'))
                    ]),

                    Group::make([
                        Input::make('events.link')
                            ->title(__('events.screen.input.link.title'))
                            ->placeholder(__('events.screen.input.link.placeholder'))
                            ->help(__('events.screen.input.link.help'))
                            ->disabled($this->events->status == "CANCELLED"),
                    ]),

                    Cropper::make('events.banner')
                        ->title(__('events.screen.input.banner.title'))
                        ->actionId($this->events->event_id)
                        ->remoteTag('banners')
                        ->minWidth(750)
                        ->maxWidth(1500)
                        ->minHeight(250)
                        ->maxHeight(500)
                        ->maxFileSize(200)
                ],
                'Weather' => [
                    Layout::metrics([
                        'Summary' => 'metrics.summary',
                        'Temperature' => 'metrics.temperature',
                        'Wind' => 'metrics.wind',
                        'Precipitations' => 'metrics.precipitation',
                    ])->canSee($this->events->exists),
                ]
            ])->activeTab('Information')
        ];
    }

    public function createOrUpdate(Request $request) {

        $this->events->fill($request->get('events'))->save();

        Toast::info(__('events.screen.toast.created'));

        event(new UpdateAudit("event", $this->events->name . " updated.", Auth::user()->name));

        if (env('APP_TEST_MAIL', false)) {
            $users = User::all();
            foreach ($users as $user) {
                Mail::to($user->email)->send(new ScheduleMail());
            }
        } else {
            Mail::to('vakea@fluffici.eu')->send(new ScheduleMail());
        }

        return redirect()->route('platform.events.list');
    }

    public function cancel() {

        Events::updateOrCreate(
            ['event_id' => $this->events->event_id],
            [
                'status' => "CANCELLED"
            ]
        );

        Toast::info(__('events.screen.toast.cancel', ['name' => $this->events->name]));


        event(new UpdateAudit("event", $this->events->name . " set a cancelled.", Auth::user()->name));

        return redirect()->route('platform.events.list');
    }

    public function finish() {

        Events::updateOrCreate(
            ['event_id' => $this->events->event_id],
            [
                'status' => "ENDED"
            ]
        );

        Toast::info(__('events.screen.toast.finish', ['name' => $this->events->name]));


        event(new UpdateAudit("event", $this->events->name . " set a finished.", Auth::user()->name));

        return redirect()->route('platform.events.list');
    }

    public function undo() {
        Events::updateOrCreate(
            ['event_id' => $this->events->event_id],
            [
                'status' => "INCOMING"
            ]
        );

        Toast::info(__('events.screen.toast.undo', ['name' => $this->events->name]));

        event(new UpdateAudit("event", "Undone last changes " . $this->events->name, Auth::user()->name));

        return redirect()->route('platform.events.list');
    }

    /**
     * Get the banner attachment ID for a given event ID.
     *
     * @param int $id The event ID.
     * @return int|null The banner attachment ID or NULL if it doesn't exist.
     */
    private function getBanner($id) {
        if ($this->events->exists) {
            return PlatformAttachments::where('action_id', $id)->firstOrFail()->attachment_id ?: NULL;
        } else {
            return NULL;
        }
    }

    /**
     * Fetches the current temperature from a weather API.
     *
     * @return float The current temperature in Celsius.
     */
    private function getTemperature(): float
    {
        if (!$this->events->exists)
            return 0.0;

        $response = $this->sendRequest('https://www.meteosource.com/api/v1/free/point?lat=' . $this->events->min['lat'] . '&lon=' . $this->events->min['lng'] .'&timezone=UTC&language=cs&units=metric');
        if ($response->code === 200) {
            return json_decode($response->body, true)['current']['temperature'];
        }

        return 0.0;
    }

    /**
     * Get the summary of the current weather conditions.
     *
     * @return string The summary of the current weather conditions.
     */
    private function getSummary(): string
    {
        if (!$this->events->exists)
            return 'Nominal';

        $response = $this->sendRequest('https://www.meteosource.com/api/v1/free/point?lat=' . $this->events->min['lat'] . '&lon=' . $this->events->min['lng'] .'&timezone=UTC&language=cs&units=metric');
        if ($response->code === 200) {
            return json_decode($response->body, true)['current']['summary'];
        }

        return "Nominal";
    }

    /**
     * Get the icon for the summary based on the icon number.
     *
     * @return string The icon name.
     * @throws ConnectionErrorException If there is an error in the connection.
     */
    private function getSummaryIcon(): string
    {
        if (!$this->events->exists)
            return 'bs.patch-question';

        $iconMap = [
            1 => 'bs.patch-question',
            2 => 'bs.brightness-high',
            3 => 'bs.cloud-sun',
            4 => 'bs.cloud-sun',
            5 => 'bs.clouds',
            6 => 'bs.clouds',
            7 => 'bs.cloudy',
            8 => 'bs.cloud-download',
            9 => 'bs.cloud-fog2',
            10 => 'bs.cloud-drizzle',
            11 => 'bs.cloud-rain',
            12 => 'bs.cloud-drizzle',
            13 => 'bs.cloud-rain-heavy',
            14 => 'bs.cloud-lightning',
            15 => 'bs.cloud-lightning',
            16 => 'bs.cloud-snow',
            17 => 'bs.cloud-snow',
            18 => 'bs.cloud-snow',
            19 => 'bs.cloud-snow',
            20 => 'bs.cloud-hail',
            21 => 'bs.cloud-hail',
            22 => 'bs.cloud-hail',
            23 => 'bs.cloud-hail',
            24 => 'bs.cloud-hail',
            25 => 'bs.cloud-hail',
            26 => 'bs.moon-stars',
            27 => 'bs.cloud-moon',
            28 => 'bs.cloud-moon',
            29 => 'bs.cloud-moon',
            30 => 'bs.cloud-moon',
            31 => 'bs.cloud-moon',
            32 => 'bs.cloud-rain',
            33 => 'bs.cloud-lightning',
            34 => 'bs.cloud-snow',
            35 => 'bs.cloud-snow',
            36 => 'bs.cloud-snow',
        ];

        $response = $this->sendRequest('https://www.meteosource.com/api/v1/free/point?lat=' . $this->events->min['lat'] . '&lon=' . $this->events->min['lng'] . '&timezone=UTC&language=cs&units=metric');
        if ($response->code === 200) {
            $index = json_decode($response->body, true)['current']['icon_num'];
            return $iconMap[$index] ?? 'bs.patch-question';
        }
        return "bs.patch-question";
    }

    /**
     * Get the wind index for the current events.
     *
     * @return string The wind index, or "Nominal" if there was an error fetching the data.
     */
    private function getWindIndex(): string
    {
        if (!$this->events->exists)
            return "Nominal";

        $response = $this->sendRequest('https://www.meteosource.com/api/v1/free/point?lat=' . $this->events->min['lat'] . '&lon=' . $this->events->min['lng'] .'&timezone=UTC&language=cs&units=metric');
        if ($response->code === 200) {
            return json_decode($response->body, true)['current']['wind'];
        }

        return "Nominal";
    }

    /**
     * Get the wind icon based on the current wind angle.
     * Uses the Meteosource API to fetch wind information.
     *
     * @return string The name of the wind icon.
     * @throws ConnectionErrorException
     */
    private function getWindIcon(): string
    {
        if (!$this->events->exists)
            return "bs.patch-question";

        $response = $this->sendRequest('https://www.meteosource.com/api/v1/free/point?lat=' . $this->events->min['lat'] . '&lon=' . $this->events->min['lng'] .'&timezone=UTC&language=cs&units=metric');
        if ($response->code === 200) {
            $windAngle = json_decode($response->body, true)['current']['wind']['angle'];
            if ($windAngle >= 0 && $windAngle < 45) {
                return "bs.arrow-up-short";
            } elseif ($windAngle >= 45 && $windAngle < 90) {
                return "bs.arrow-up-right";
            } elseif ($windAngle >= 90 && $windAngle < 135) {
                return "bs.arrow-down-right";
            } elseif ($windAngle >= 135 && $windAngle < 180) {
                return "bs.arrow-down-short";
            } elseif ($windAngle >= 180 && $windAngle < 225) {
                return "bs.arrow-down-left";
            } elseif ($windAngle >= 225 && $windAngle < 270) {
                return "bs.arrow-left-short";
            } elseif ($windAngle >= 270 && $windAngle < 315) {
                return "bs.arrow-up-left";
            } else {
                return "bs.arrow-up-short";
            }
        }

        return "bs.patch-question";
    }

    private function getPrecipitation(): int
    {
        if (!$this->events->exists)
            return 0;

        $response = $this->sendRequest('https://www.meteosource.com/api/v1/free/point?lat=' . $this->events->min['lat'] . '&lon=' . $this->events->min['lng'] .'&timezone=UTC&language=cs&units=metric');
        if ($response->code === 200) {
            return json_decode($response->body, true)['current']['precipitation']['total'];
        }

        return 0;
    }

    /**
     * Send a request to the given URL and return the response.
     *
     * @param string $url The URL to send the request to.
     * @return \Httpful\Response The response from the request.
     * @throws ConnectionErrorException
     */
    private function sendRequest(string $url): \Httpful\Response
    {
        return \Httpful\Request::get($url . '&key=' . env('WEATHER_API_SECRET', ''), "application/json")->expectsJson()->send();
    }
}
