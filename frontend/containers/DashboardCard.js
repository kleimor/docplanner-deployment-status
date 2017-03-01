import React from "react";
import {fetchCommits} from "../actions/commits";
import {fetchStatuses} from "../actions/statuses";
import {toggleStarred} from "../actions/starred";
import {connect} from "react-redux";
import RelativeTime from "../components/RelativeTime";
import Stage from "./Stage";
import {fetchLatestDeployment} from "../actions/deployments";
import {fetchCommitsDiff} from "../actions/commits_diff";
import {loadProjectData, reloadProjectData} from "../helpers/utilities";
import LiveUpdateIndicator from "./LiveUpdateIndicator";

class DashboardCard extends React.Component {
	componentDidMount () {
		loadProjectData(this.props.owner, this.props.repo);
		jQuery(this.refs.starButton).tooltip();
	}

	componentDidUpdate () {
		jQuery(this.refs.starButton).tooltip();
	}

	componentWillUpdate() {
		jQuery(this.refs.starButton).tooltip('dispose');
	}

	componentWillUnmount() {
		jQuery(this.refs.starButton).tooltip('dispose');
	}

	reloadProject () {
		reloadProjectData(this.props.owner, this.props.repo);
	}

	toggleStarred () {
		const {owner, repo} = this.props;

		this.props.toggleStarred(owner, repo);
	};

	getOverallState () {
		// TODO: move this to utilities
		for (let stage in this.props.commits) {
			const stageCommits = this.props.commits[stage];
			if (null === stageCommits) {
				continue;
			}
			if (stageCommits.isLoading) {
				return "faded";
			} else if (false === stageCommits.isRecent) {
				return "danger"
			} else if (stageCommits.hasOwnProperty("commits")) {
				if (0 === stageCommits.commits.length) {
					return "danger";
				}
			}
		}
		for (let stage in this.props.commitsDiff) {
			const stageCommitsDiff = this.props.commitsDiff[stage];
			if (null === stageCommitsDiff) {
				continue;
			}
			if (stageCommitsDiff.isLoading) {
				return "faded";
			} else if (false === stageCommitsDiff.isRecent) {
				if (false === (
						this.props.deployments.hasOwnProperty(stage)
						&& this.props.deployments[stage].isRecent
						&& this.props.deployments[stage].latestDeployment
					)
				) {
					return "danger";
				}
			} else if (stageCommitsDiff.diff && parseInt(stageCommitsDiff.diff.ahead_by) > 0)
				return "warning";
		}
		for (let stage in this.props.statuses) {
			const stageStatuses = this.props.statuses[stage];
			if (null === stageStatuses) {
				continue;
			}
			if (stageStatuses.isLoading) {
				return "";
			}
			else {
				if (false === stageStatuses.isRecent) {
					return "warning"
				}
				if (stageStatuses.statuses.length) {
					let latestStatusState = stageStatuses.statuses[0].state;
					if ("failure" === latestStatusState) {
						return "danger";
					}
				}
			}
		}

		return "success";
	}

	render = () => {
		const stages = this.props.stages.map((stage) => (
			<Stage
				project={this.props.project}
				stage={stage}
				commits={this.props.commits[stage.name]}
				commitsDiff={this.props.commitsDiff[stage.name]}
				statuses={this.props.statuses[stage.name]}
				deployments={this.props.deployments[stage.name]}
			></Stage>
		));

		let latestChange;
		for (let stage in this.props.commits) {
			if (this.props.commits.hasOwnProperty(stage)) {
				const commitUpdatedAt = this.props.commits[stage].updatedAt;
				latestChange = ("undefined" === typeof latestChange || commitUpdatedAt > latestChange) ? commitUpdatedAt : latestChange;
			}
		}
		for (let stage in this.props.statuses) {
			if (this.props.statuses[stage]) {
				const statusUpdatedAt = this.props.statuses[stage].updatedAt;
				latestChange = ("undefined" === typeof latestChange || statusUpdatedAt > latestChange) ? statusUpdatedAt : latestChange;
			}
		}
		for (let stage in this.props.deployments) {
			if (this.props.deployments[stage]) {
				const deploymentUpdatedAt = this.props.deployments[stage].updatedAt;
				latestChange = ("undefined" === typeof latestChange || deploymentUpdatedAt > latestChange) ? deploymentUpdatedAt : latestChange;
			}
		}

		const overallState = this.getOverallState();

		return (
			<div className="col-sm-6 col-md-6 col-lg-4 col-xl-3 p-0">
				<div className={`card card-outline-${overallState} mx-1 mb-2`}>
					<div className={`card-header text-center bg-${overallState}`}>
						<a
							className={"faded" === overallState ? "text-primary" : "text-white"}
							href={`https://github.com/${this.props.owner}/${this.props.repo}`}
							target="_blank"
						>
							<strong>{this.props.owner}/{this.props.repo}</strong>
						</a>
					</div>
					<div className="card-block p-0">
						<ul className="list-group list-group-flush">
							<li className="list-group-item px-2 py-0 text-muted">
								<div className="container-fluid w-100">
									<div className="row">
										<div className="col-5 p-0 text-left">
											<small className="text-uppercase">Stage</small>
										</div>
										<div className="col-3 p-0 text-center">
											<small className="text-uppercase">Commit</small>
										</div>
										<div className="col-1 p-0 text-center">
											<small className="text-uppercase">CI</small>
										</div>
										<div className="col-3 p-0 text-center">
											<small className="text-uppercase">Deploy</small>
										</div>
									</div>
								</div>
							</li>
							{stages}
						</ul>
					</div>
					<div className="card-footer text-muted">
						<div className="row">
							<div className="col-4 text-left">
								<button
									ref="starButton"
									className="btn btn-link btn-sm p-0 mr-1"
									onClick={this.toggleStarred.bind(this)}
									data-toggle="tooltip"
									data-placement="right"
									data-html="true"
									title={`<small>${this.props.isStarred ? "Unstar project" : "Star project"}</small>`}
								>
								<span className={`md-icon ${this.props.isStarred ? "text-warning" : "text-muted"}`}>
									{this.props.isStarred ? "star" : "star_border"}
								</span>
								</button>
							</div>
							<div className="col-4 text-center">
								<LiveUpdateIndicator
									project={this.props.project}
									hooks={this.props.hooks}
								/>
							</div>
							<div className="col-4 text-right">
								<small className="font-italic">
									<RelativeTime
										date={latestChange}
										onClick={this.reloadProject.bind(this)}
									/>
								</small>
							</div>
						</div>
					</div>
				</div>
			</div>
		)
	}
}

const mapStateToProps = (state, ownProps) => {
	let commits = {};
	ownProps.stages.forEach((stage) => {
		let key = `${ownProps.owner}/${ownProps.repo}/${stage.name}`;
		commits[stage.name] = state.commits.forProject.hasOwnProperty(key) ?
			state.commits.forProject[key]
			:
			[];
	});
	let commitsDiff = {};
	ownProps.stages.forEach((stage) => {
		let key = `${ownProps.owner}/${ownProps.repo}/${stage.name}`;
		commitsDiff[stage.name] = state.commitsDiff.forProject.hasOwnProperty(key) ?
			state.commitsDiff.forProject[key]
			:
			[];
	});
	let statuses = {};
	ownProps.stages.forEach((stage) => {
		let key = `${ownProps.owner}/${ownProps.repo}/${stage.name}`;
		statuses[stage.name] = state.statuses.forProject.hasOwnProperty(key) ?
			state.statuses.forProject[key]
			:
			null;
	});
	let deployments = {};
	ownProps.stages.forEach((stage) => {
		let key = `${ownProps.owner}/${ownProps.repo}/${stage.name}`;
		deployments[stage.name] = state.deployments.forProject.hasOwnProperty(key) ?
			state.deployments.forProject[key]
			:
			null;
	});
	const hooks = state.hooks.forProject.hasOwnProperty(`${ownProps.owner}/${ownProps.repo}`) ?
		state.hooks.forProject[`${ownProps.owner}/${ownProps.repo}`]
		:
		null;

	return {
		...ownProps,
		commits: commits,
		commitsDiff: commitsDiff,
		statuses: statuses,
		deployments: deployments,
		hooks: hooks,
		isStarred: state.starred.starred.indexOf(`${ownProps.owner}/${ownProps.repo}`) > -1,
	};
};

const mapDispatchToProps = (dispatch) => ({
	fetchCommits: (owner, repo, stage) => dispatch(fetchCommits(owner, repo, stage)),
	fetchCommitsDiff: (owner, repo, stage) => dispatch(fetchCommitsDiff(owner, repo, stage)),
	fetchStatuses: (owner, repo, stage) => dispatch(fetchStatuses(owner, repo, stage)),
	fetchLatestDeployment: (owner, repo, stage) => dispatch(fetchLatestDeployment(owner, repo, stage)),
	toggleStarred: (owner, repo) => dispatch(toggleStarred(owner, repo)),
});

export default connect(mapStateToProps, mapDispatchToProps)(DashboardCard);
