import DashboardController from './DashboardController'
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
import FriendController from './FriendController'
import FishingLogsController from './FishingLogsController'
import Settings from './Settings'

const Controllers = {
    DashboardController: Object.assign(DashboardController, DashboardController),
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
    FriendController: Object.assign(FriendController, FriendController),
    FishingLogsController: Object.assign(FishingLogsController, FishingLogsController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers