import DashboardController from './DashboardController'
import LocationController from './LocationController'
import RodController from './RodController'
import FishController from './FishController'
import FlyController from './FlyController'
import FriendController from './FriendController'
import FishingLogController from './FishingLogController'
import Settings from './Settings'

const Controllers = {
    DashboardController: Object.assign(DashboardController, DashboardController),
    LocationController: Object.assign(LocationController, LocationController),
    RodController: Object.assign(RodController, RodController),
    FishController: Object.assign(FishController, FishController),
    FlyController: Object.assign(FlyController, FlyController),
    FriendController: Object.assign(FriendController, FriendController),
    FishingLogController: Object.assign(FishingLogController, FishingLogController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers