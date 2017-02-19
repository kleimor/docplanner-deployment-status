import React from 'react'
import moment from 'moment'
import * as momentTimer from 'moment-timer'
import * as MomentShortFormat from 'moment-shortformat'

class RelativeTime extends React.Component {
	updateDescriptions = () => {
		let thisMoment = moment(this.props.date);
		this.setState({
			formattedLong: thisMoment.format("LLL Z"),
			formatted: thisMoment.short(this.props.withoutSuffix)
		});
	};

	componentWillMount () {
		this.updateDescriptions();
	}

	componentDidMount () {
		this.setState({
			timer: moment.duration({seconds: 5}).timer({
				loop: true,
				start: true
			}, this.updateDescriptions)
		});
		jQuery(this.refs.timeElement).tooltip();
	}

	componentDidUpdate () {
		jQuery(this.refs.timeElement).tooltip();
	}

	componentWillUnmount () {
		this.state.timer.stop();
	}

	render = () => {
		const timeElement = (
			<span
				ref="timeElement"
				data-toggle="tooltip"
				data-placement="left"
				data-html="true"
				title={"<small>" + this.state.formattedLong + "</small>"}
			>
				{this.props.children}
				{this.state.formatted}
			</span>
		);
		if (typeof this.props.onClick !== "function") {
			return timeElement;
		}

		return (
			<button className="btn btn-link btn-sm text-muted" onClick={this.props.onClick}>
				{timeElement}
			</button>
		);

	}
}

RelativeTime.propTypes = {
	date: React.PropTypes.instanceOf(Date).isRequired,
	onClick: React.PropTypes.func,
	withoutSuffix: React.PropTypes.bool
};

RelativeTime.defaultProps = {
	withoutSuffix: false,
};

export default RelativeTime;
