import ProfileController from './ProfileController'
import PasswordController from './PasswordController'
import FollowingSettingsController from './FollowingSettingsController'
import TwoFactorAuthenticationController from './TwoFactorAuthenticationController'
import SubscriptionController from './SubscriptionController'

const Settings = {
    ProfileController: Object.assign(ProfileController, ProfileController),
    PasswordController: Object.assign(PasswordController, PasswordController),
    FollowingSettingsController: Object.assign(FollowingSettingsController, FollowingSettingsController),
    TwoFactorAuthenticationController: Object.assign(TwoFactorAuthenticationController, TwoFactorAuthenticationController),
    SubscriptionController: Object.assign(SubscriptionController, SubscriptionController),
}

export default Settings