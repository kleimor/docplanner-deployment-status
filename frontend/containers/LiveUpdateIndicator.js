import React from "react"
import {connect} from "react-redux"
import {installHook} from "../actions/hooks";

class LiveUpdateIndicator extends React.Component {
	componentDidMount () {
		jQuery(this.refs.liveUpdate).tooltip();
	}

	componentDidUpdate () {
		jQuery(this.refs.liveUpdate).tooltip();
	}

	installHooks () {
		const {project} = this.props;

		jQuery(this.refs.starButton).tooltip('hide');
		this.props.installHook(project.owner, project.repo);
	}

	render = () => {
		const {hooks} = this.props;

		if (!hooks || hooks.isLoading) {
			return (
				<span className="md-icon">more_horiz</span>
			)
		}

		if (hooks.hooks.length > 0) {
			return (
				<span
					ref="liveUpdate"
					className="md-icon text-success"
					data-toggle="tooltip"
					data-placement="top"
					data-html="true"
					title="<small>Live update enabled</small>"
				>
					wifi_tethering
				</span>
			);
		} else {
			return (
				<button
					ref="liveUpdate"
					className="btn btn-link btn-sm"
					data-toggle="tooltip"
					data-placement="top"
					data-html="true"
					title="<small>Live-update disabled<br />Click to enable</small>"
					onClick={this.installHooks.bind(this)}
				>
					<span className="md-icon text-danger">portable_wifi_off</span>
				</button>
			);
		}
	}
}

const mapStateToProps = (state, ownProps) => ({
	...ownProps,
});

const mapDispatchToProps = (dispatch) => ({
	installHook: (owner, repo) => dispatch(installHook(owner, repo)),
});

export default connect(mapStateToProps, mapDispatchToProps)(LiveUpdateIndicator);
