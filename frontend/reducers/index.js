import {combineReducers} from "redux";
import projectsReducer from "./projects"
import commitsReducer from "./commits"
import statusesReducer from "./statuses"
import starredReducer from "./starred"

const rootReducer = combineReducers({
	projects: projectsReducer,
	commits: commitsReducer,
	statuses: statusesReducer,
	starred: starredReducer,
});

export default rootReducer;
