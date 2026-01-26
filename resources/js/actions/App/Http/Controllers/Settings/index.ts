import ProfileController from './ProfileController'
import PasswordController from './PasswordController'
import FollowingSettingsController from './FollowingSettingsController'
import TwoFactorAuthenticationController from './TwoFactorAuthenticationController'

const Settings = {
    ProfileController: Object.assign(ProfileController, ProfileController),
    PasswordController: Object.assign(PasswordController, PasswordController),
    FollowingSettingsController: Object.assign(FollowingSettingsController, FollowingSettingsController),
    TwoFactorAuthenticationController: Object.assign(TwoFactorAuthenticationController, TwoFactorAuthenticationController),
}

export default Settings