# Tokbox frequently encountered issues

# Api credentials rejected
If credentials are rejected, empty video_conference token in order to regenerate tokens:

    truncate video_conference_token;

If credentials are still rejected, check the time of VM. When time is to late compared to real time, credentials generation may be refused.

# Offical documentation
https://tokbox.com/developer/