import React from "react";
import {IndexRoute, Route} from "react-router";
import App from "./containers/App";
import Dashboard from "./containers/Dashboard";

export default (
	<Route path="/" component={App}>
		<IndexRoute component={Dashboard}/>
		{/*<Route path="/detailed" component={Repos}>*/}
		{/*<Route path="/detailed/:owner/:repo" component={Repo}/>*/}
		{/*</Route>*/}
		{/*<Route path="*" component={NoMatch}/>*/}
	</Route>
)
