var inBrowser = typeof window !== 'undefined';
var UserAgent = inBrowser && window.navigator.userAgent.toLowerCase();
var isEdge = UserAgent && UserAgent.indexOf('edge/') > 0;

export default isEdge;
