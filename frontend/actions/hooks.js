import axios from "axios";

export const FETCH_HOOKS_STARTED = 'FETCH_HOOKS_STARTED';
export const FETCH_HOOKS_FINISHED = 'FETCH_HOOKS_FINISHED';
export const FETCH_HOOKS_FAILED = 'FETCH_HOOKS_FAILED';

export const INSTALL_HOOK_STARTED = 'INSTALL_HOOK_STARTED';
export const INSTALL_HOOK_FINISHED = 'INSTALL_HOOK_FINISHED';
export const INSTALL_HOOK_FAILED = 'INSTALL_HOOK_FAILED';

const fetchHooksStarted = (owner, repo) => ({
	type: FETCH_HOOKS_STARTED,
	owner: owner,
	repo: repo,
});

const fetchHooksFinished = (owner, repo, hooks) => ({
	type: FETCH_HOOKS_FINISHED,
	owner: owner,
	repo: repo,
	hooks: hooks,
});

const fetchHooksFailed = (owner, repo, error) => ({
	type: FETCH_HOOKS_FAILED,
	owner: owner,
	repo: repo,
	error: error,
});

export const fetchHooks = (owner, repo) => (dispatch) => {
	dispatch(fetchHooksStarted(owner, repo));

	return axios
		.get(`/api/1/projects/${owner}/${repo}/hooks`)
		.then((response) => dispatch(fetchHooksFinished(owner, repo, response.data)))
		.catch((error) => dispatch(fetchHooksFailed(owner, repo, error)));
};

const installHookStarted = (owner, repo) => ({
	type: INSTALL_HOOK_STARTED,
	owner: owner,
	repo: repo,
});

const installHookFinished = (owner, repo) => ({
	type: INSTALL_HOOK_FINISHED,
	owner: owner,
	repo: repo,
});

const installHookFailed = (owner, repo, error) => ({
	type: INSTALL_HOOK_FAILED,
	owner: owner,
	repo: repo,
	error: error,
});

export const installHook = (owner, repo) => (dispatch) => {
	dispatch(installHookStarted(owner, repo));

	return axios
		.get(`/api/1/projects/${owner}/${repo}/hooks`)
		.then((response) => dispatch(installHookFinished(owner, repo)))
		.catch((error) => dispatch(installHookFailed(owner, repo, error)));
};
