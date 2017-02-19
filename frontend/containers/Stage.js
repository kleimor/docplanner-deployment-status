import React from "react";
import {connect} from "react-redux";
import jQuery from "jquery";

class Stage extends React.Component {
	componentDidMount () {
		jQuery(this.refs.commitBadge).tooltip();
		jQuery(this.refs.statusBadge).tooltip();
	}

	componentDidUpdate () {
		jQuery(this.refs.commitBadge).tooltip();
		jQuery(this.refs.statusBadge).tooltip();
	}

	formatMessage (message) {
		let formatterMessage = jQuery("<div>").text(message).html();
		return formatterMessage.replace(/([^>\r\n]?)((?:\r\n|\n\r|\r|\n)+)/g, '$1<br ' + '/>$2');
	}

	render = () => {
		const {stage, commits, statuses} = this.props;

		let commitHtml = "";
		if (commits.isLoading) {
			commitHtml = (
				<span className="md-icon rotating pr-1 text-warning">sync</span>
			);
		} else {
			if (commits.commits && commits.commits.length) {
				const latestCommit = commits.commits[0];
				commitHtml = (
					<a href={commits.commits[0].html_url}>
					<span
						ref="commitBadge"
						className="badge badge-success"
						data-toggle="tooltip"
						data-placement="top"
						data-html="true"
						title={`
							<small class="text-left">
								${this.formatMessage(latestCommit.commit.message)}
								<br />
								${this.formatMessage(latestCommit.author.login)}
							</small>
						`}
					>
						{latestCommit.sha.substr(0, 6)}
					</span>
					</a>
				);
			} else {
				commitHtml = (
					<span ref="commitBadge" className="md-icon pr-1 text-danger">warning</span>
				);
			}
		}

		let statusHtml = <span className="md-icon pr-1 text-danger">warning</span>;
		if (statuses) {
			if (statuses.isLoading) {
				statusHtml = <span className="md-icon rotating pr-1 text-warning">sync</span>;
			}
			else {
				const latestStatus = statuses.statuses.length ?
					statuses.statuses[0]
					:
					null
				;
				if (latestStatus) {
					statusHtml = (
						<a href={latestStatus.target_url} title={latestStatus.description} target="_blank">
							<span
								ref="statusBadge"
								className={{
									pending: "badge badge-warning",
									success: "badge badge-success",
									failure: "badge badge-danger",
								}[latestStatus.state]}
								data-toggle="tooltip"
								data-placement="top"
								data-html="true"
								title={`<small>${latestStatus.description}</small>`}
							>
								{latestStatus.context}
							</span>
						</a>
					);
				}
			}
		}

		return (
			<li className="list-group-item">
				<div className="container-fluid w-100">
					<div className="row justify-content-between">
						<div className="col-4 p-0 text-left">{stage.name}</div>
						<div className="col-4 p-0 text-center">{commitHtml}</div>
						<div className="col-4 p-0 text-right">{statusHtml}</div>
					</div>
				</div>
			</li>
		);
	}
}

const mapStateToProps = (state, ownProps) => {
	return {
		...ownProps
	};
};

const mapDispatchToProps = (dispatch) => ({});

export default connect(mapStateToProps, mapDispatchToProps)(Stage);
