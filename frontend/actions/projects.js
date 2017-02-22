import * as jQuery from "jquery";

export const FETCH_PROJECTS_STARTED = 'FETCH_PROJECTS_STARTED';
export const FETCH_PROJECTS_FINISHED = 'FETCH_PROJECTS_FINISHED';
export const FETCH_PROJECTS_FAILED = 'FETCH_PROJECTS_FAILED';
export const REMOVE_PROJECT = 'REMOVE_PROJECT';

const fetchProjectsStarted = () => ({
	type: FETCH_PROJECTS_STARTED
});

const fetchProjectsFinished = (projects) => ({
	type: FETCH_PROJECTS_FINISHED,
	projects: projects,
});

const fetchProjectsFailed = (error) => ({
	type: FETCH_PROJECTS_FAILED,
	error: error
});

export const fetchProjects = () => (dispatch) => {
	dispatch(fetchProjectsStarted());

	jQuery.ajax({
		url: "/api/1/projects",
		dataType: 'json',
		success: (data) => {
			dispatch(fetchProjectsFinished(data));
		},
		error: (data) => {
			dispatch(fetchProjectsFailed(data))
		}
	});
};

export const removeProject = (owner, repo) => (dispatch) => {
	dispatch({
		type: REMOVE_PROJECT,
		owner: owner,
		repo: repo
	});
};

export const clearProjectCache = (owner, repo, onCacheCleared = () => {}) => {
	jQuery.ajax({
		url: `/api/1/projects/${owner}/${repo}/cache`,
		method: 'DELETE',
		success: () => {
			onCacheCleared();
		}
	});
};
