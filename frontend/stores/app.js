module.exports = require(process.env.NODE_ENV === 'production' ? './app.prod' : './app.dev');
