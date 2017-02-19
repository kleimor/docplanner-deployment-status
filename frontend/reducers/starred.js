import {TOGGLE_STARRED} from "../actions/starred"

const initialState = {
	starred: JSON.parse(localStorage.getItem('starred')) || [],
};

const starredReducer = (state = initialState, action) => {
	switch (action.type) {
		case TOGGLE_STARRED:
			return (() => {
				let newState = {...state};
				let key = `${action.owner}/${action.repo}`;
				let index = newState.starred.indexOf(key);
				if (index > -1) {
					newState.starred.splice(index, 1);
				}
				else {
					newState.starred.push(key);
				}

				localStorage.setItem('starred', JSON.stringify(state.starred));

				return newState;
			})();
			break;
	}

	return state;
};

export default starredReducer;
