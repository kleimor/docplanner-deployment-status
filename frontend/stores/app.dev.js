import {createStore, applyMiddleware} from 'redux';
import thunk from 'redux-thunk';
import rootReducer from '../reducers/index';
import {composeWithDevTools} from "redux-devtools-extension";

const initialState = {};

const composeEnhancers = composeWithDevTools({
	serialize: true,
});

const appStore = createStore(
	rootReducer,
	initialState,
	composeEnhancers(applyMiddleware(thunk)),
);

export default appStore;
