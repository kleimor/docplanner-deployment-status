const path = require('path');
const webpack = require('webpack');

const config = {
	entry: {
		main: './frontend/index.js',
	},
	externals: {
		"jquery": 'jQuery',
		"moment": 'moment',
		"pusher-js": 'Pusher',
		"react": 'React',
		"react-dom": 'ReactDOM',
		// "react-router": ["Router", "Route", "Link"]
	},
	output: {
		path: path.resolve(__dirname, 'web/app/'),
		publicPath: "/app/",
		filename: '[name].js'
	},
	module: {
		rules: [
			{
				test: /\.(js|jsx)$/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: [
							"es2015",
							"stage-2",
							"react"
						]
					}
				},
			},
			{
				test: /\.css$/,
				use: [
					'style-loader',
					'css-loader?modules',
				]
			}
		]
	},
	plugins: [
		new webpack.DefinePlugin({
			'process.env': {
				NODE_ENV: JSON.stringify('production')
			}
		}),
		new webpack.optimize.UglifyJsPlugin(),
	]
};

module.exports = config;
