import {createStore, applyMiddleware} from 'redux';
import thunk from 'redux-thunk';
import rootReducer from '../reducers/index';

const initialState = {};

const appStore = createStore(
	rootReducer,
	initialState,
	applyMiddleware(thunk),
);

export default appStore;
