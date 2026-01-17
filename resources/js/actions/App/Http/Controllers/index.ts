import LocationController from './LocationController'
import EquipmentController from './EquipmentController'
import FishController from './FishController'
import FlyController from './FlyController'
import FriendController from './FriendController'
import FishingLogController from './FishingLogController'
import Settings from './Settings'

const Controllers = {
    LocationController: Object.assign(LocationController, LocationController),
    EquipmentController: Object.assign(EquipmentController, EquipmentController),
    FishController: Object.assign(FishController, FishController),
    FlyController: Object.assign(FlyController, FlyController),
    FriendController: Object.assign(FriendController, FriendController),
    FishingLogController: Object.assign(FishingLogController, FishingLogController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers