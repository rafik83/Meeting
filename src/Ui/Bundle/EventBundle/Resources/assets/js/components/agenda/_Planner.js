import Group from "./_Group";

/**
 * Planner
 */
function Planner() {
    this.meets  = [];
    this.groups = [];

    this.clearMeetGroup   = this.clearMeetGroup.bind(this);
    this.resolveMeetGroup = this.resolveMeetGroup.bind(this);
    this.resolveGroup     = this.resolveGroup.bind(this);
    this.displayMeet      = this.displayMeet.bind(this);
}

/**
 * Set meets
 *
 * @param {Array} meets A list of Meet
 */
Planner.prototype.setMeets = function(meets) {
    this.meets = meets;
    this.groups.length = 0;
    this.meets.forEach(this.clearMeetGroup);
    this.meets.forEach(this.resolveMeetGroup);
    this.groups.forEach(this.resolveGroup);
    this.meets.forEach(this.displayMeet);
};

/**
 * Clear meet group
 *
 * @param {Meet} meet
 */
Planner.prototype.clearMeetGroup = function(meet) {
    meet.setGroup(null);
};

/**
 * Resolve Meet Group
 *
 * @param {Meet} meet
 */
Planner.prototype.resolveMeetGroup = function(meet) {
    if (meet.group) {
        return;
    }

    var length = this.meets.length;

    for (var target, i = 0; i < length; i++) {
        target = this.meets[i];

        if (target === meet) {
            continue;
        }

        if (target.overlap(meet)) {
            if (target.group) {
                return target.group.add(meet);
            } else {
                var group = this.createGroup();

                group.add(target);

                return group.add(meet);
            }
        }
    }

    return this.createGroup().add(meet);
};

/**
 * Create group
 *
 * @return {Group}
 */
Planner.prototype.createGroup = function() {
    var group = new Group();

    this.groups.push(group);

    return group;
};

/**
 * Resolve Group
 *
 * @param {Group} group
 */
Planner.prototype.resolveGroup = function(group) {
    group.resolve();
};

/**
 * Display meet
 *
 * @param {Meet} meet
 */
Planner.prototype.displayMeet = function(meet) {
    meet.display();
};

export default Planner;
