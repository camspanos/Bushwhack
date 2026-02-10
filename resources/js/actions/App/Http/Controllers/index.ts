import DashboardController from './DashboardController'
import DashboardPreferencesController from './DashboardPreferencesController'
import BadgesController from './BadgesController'
import LeaderboardController from './LeaderboardController'
import FollowingController from './FollowingController'
import PublicDashboardController from './PublicDashboardController'
import PublicRodsController from './PublicRodsController'
import PublicFishController from './PublicFishController'
import PublicFliesController from './PublicFliesController'
import UserLocationsController from './UserLocationsController'
import UserRodsController from './UserRodsController'
import UserFishController from './UserFishController'
import UserFliesController from './UserFliesController'
import UserFriendsController from './UserFriendsController'
import FishingLogsController from './FishingLogsController'
import Settings from './Settings'

const Controllers = {
    DashboardController: Object.assign(DashboardController, DashboardController),
    DashboardPreferencesController: Object.assign(DashboardPreferencesController, DashboardPreferencesController),
    BadgesController: Object.assign(BadgesController, BadgesController),
    LeaderboardController: Object.assign(LeaderboardController, LeaderboardController),
    FollowingController: Object.assign(FollowingController, FollowingController),
    PublicDashboardController: Object.assign(PublicDashboardController, PublicDashboardController),
    PublicRodsController: Object.assign(PublicRodsController, PublicRodsController),
    PublicFishController: Object.assign(PublicFishController, PublicFishController),
    PublicFliesController: Object.assign(PublicFliesController, PublicFliesController),
    UserLocationsController: Object.assign(UserLocationsController, UserLocationsController),
    UserRodsController: Object.assign(UserRodsController, UserRodsController),
    UserFishController: Object.assign(UserFishController, UserFishController),
    UserFliesController: Object.assign(UserFliesController, UserFliesController),
    UserFriendsController: Object.assign(UserFriendsController, UserFriendsController),
    FishingLogsController: Object.assign(FishingLogsController, FishingLogsController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers