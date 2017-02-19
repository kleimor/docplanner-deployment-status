import React from 'react';
import ReactDOM from 'react-dom';
import {Router, hashHistory} from 'react-router';
import routes from "./routes";
import {Provider} from "react-redux";
import appStore from "./stores/app";
require("./listeners/pusher");

ReactDOM.render((
	<Provider store={appStore}>
		<Router history={hashHistory} routes={routes}>
		</Router>
	</Provider>
), document.getElementById('app'));
