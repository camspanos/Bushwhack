import DashboardController from './DashboardController'
import FollowingController from './FollowingController'
import PublicDashboardController from './PublicDashboardController'
import LocationController from './LocationController'
import RodController from './RodController'
import FishController from './FishController'
import FlyController from './FlyController'
import FriendController from './FriendController'
import FishingLogController from './FishingLogController'
import Settings from './Settings'

const Controllers = {
    DashboardController: Object.assign(DashboardController, DashboardController),
    FollowingController: Object.assign(FollowingController, FollowingController),
    PublicDashboardController: Object.assign(PublicDashboardController, PublicDashboardController),
    LocationController: Object.assign(LocationController, LocationController),
    RodController: Object.assign(RodController, RodController),
    FishController: Object.assign(FishController, FishController),
    FlyController: Object.assign(FlyController, FlyController),
    FriendController: Object.assign(FriendController, FriendController),
    FishingLogController: Object.assign(FishingLogController, FishingLogController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers