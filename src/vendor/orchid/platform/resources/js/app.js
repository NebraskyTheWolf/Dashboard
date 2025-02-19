import * as Turbo from '@hotwired/turbo';
import * as Bootstrap from 'bootstrap';
import Echo from "laravel-echo";
import * as Pusher from "pusher-js";

import { Application } from '@hotwired/stimulus';
import { definitionsFromContext } from '@hotwired/stimulus-webpack-helpers';
import ApplicationController from './controllers/application_controller';

window.Turbo = Turbo;
window.Bootstrap = Bootstrap;
window.application = Application.start();
window.Controller = ApplicationController;
window.Pusher = Pusher;

const context = require.context('./controllers', true, /\.js$/);
application.load(definitionsFromContext(context));

window.addEventListener('turbo:before-fetch-request', (event) => {
    let state = document.getElementById('screen-state').value;

    if (state.length > 0) {
        event.detail?.fetchOptions?.body?.append('_state', state)
    }
});

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '521a8d3a78ab50e2c14d',
    wsHost: 'websocket.rsiniya.uk',
    wsPort: 6001,
    forceTLS: true,
    disableStats: true,
    cluster: 'eu'
});
