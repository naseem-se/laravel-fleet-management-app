<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $legal_name
 * @property string $slug
 * @property string|null $logo_path
 * @property string $status
 * @property string $timezone
 * @property array<array-key, mixed>|null $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Subscription|null $activeSubscription
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Driver> $drivers
 * @property-read int|null $drivers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vehicle> $vehicles
 * @property-read int|null $vehicles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereLegalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company withoutTrashed()
 */
	class Company extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $company_id
 * @property int|null $user_id
 * @property string $name
 * @property string $phone
 * @property string|null $cnic_number
 * @property string|null $license_number
 * @property Carbon|null $license_expiry_date
 * @property string $status
 * @property string|null $pin_hash
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vehicle> $assignedVehicle
 * @property-read int|null $assigned_vehicle_count
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FuelEntry> $fuelEntries
 * @property-read int|null $fuel_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Journey> $journeys
 * @property-read int|null $journeys_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereCnicNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereLicenseExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereLicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver wherePinHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver withoutTrashed()
 */
	class Driver extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $company_id
 * @property int $vehicle_id
 * @property int|null $journey_id
 * @property int $driver_id
 * @property numeric $quantity_litres
 * @property numeric $rate_per_litre
 * @property numeric $total_cost
 * @property numeric $odometer_reading
 * @property string|null $receipt_photo_path
 * @property \Illuminate\Support\Carbon $entry_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Driver|null $driver
 * @property-read \App\Models\Journey|null $journey
 * @property-read \App\Models\Vehicle|null $vehicle
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereDriverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereEntryTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereJourneyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereOdometerReading($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereQuantityLitres($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereRatePerLitre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereReceiptPhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FuelEntry whereVehicleId($value)
 */
	class FuelEntry extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $company_id
 * @property int $vehicle_id
 * @property int $driver_id
 * @property string $status
 * @property numeric $start_km
 * @property string|null $start_photo_path
 * @property numeric|null $start_lat
 * @property numeric|null $start_lng
 * @property \Illuminate\Support\Carbon $start_time
 * @property numeric|null $end_km
 * @property string|null $end_photo_path
 * @property numeric|null $end_lat
 * @property numeric|null $end_lng
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property numeric|null $total_distance
 * @property int|null $duration_minutes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Driver|null $driver
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FuelEntry> $fuelEntries
 * @property-read int|null $fuel_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JourneyLocation> $locations
 * @property-read int|null $locations_count
 * @property-read \App\Models\Vehicle|null $vehicle
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereDriverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereEndKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereEndLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereEndLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereEndPhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereStartKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereStartLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereStartLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereStartPhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereTotalDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Journey whereVehicleId($value)
 */
	class Journey extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $journey_id
 * @property numeric $lat
 * @property numeric $lng
 * @property numeric|null $speed_kmh
 * @property \Illuminate\Support\Carbon $recorded_at
 * @property-read \App\Models\Journey $journey
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JourneyLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JourneyLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JourneyLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JourneyLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JourneyLocation whereJourneyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JourneyLocation whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JourneyLocation whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JourneyLocation whereRecordedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JourneyLocation whereSpeedKmh($value)
 */
	class JourneyLocation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $company_id
 * @property int $vehicle_id
 * @property string $type
 * @property string|null $description
 * @property numeric $cost
 * @property numeric|null $odometer_at_service
 * @property \Illuminate\Support\Carbon $service_date
 * @property \Illuminate\Support\Carbon|null $next_service_date
 * @property numeric|null $next_service_km
 * @property string|null $performed_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Vehicle|null $vehicle
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord whereNextServiceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord whereNextServiceKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord whereOdometerAtService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord wherePerformedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord whereServiceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRecord whereVehicleId($value)
 */
	class MaintenanceRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $company_id
 * @property string $reminder_type
 * @property string $reference_type
 * @property int $reference_id
 * @property \App\Models\Model|null $reference
 * @property Carbon|null $due_date
 * @property string|null $due_km
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereDueKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereReminderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Reminder whereUpdatedAt($value)
 */
	class Reminder extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $company_id
 * @property int $subscription_plan_id
 * @property string $status
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon $ends_at
 * @property \Illuminate\Support\Carbon|null $trial_ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\SubscriptionPlan $plan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereSubscriptionPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereUpdatedAt($value)
 */
	class Subscription extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $max_vehicles
 * @property int $max_users
 * @property numeric $price
 * @property string $billing_cycle
 * @property array<array-key, mixed>|null $features
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereBillingCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereMaxUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereMaxVehicles($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereUpdatedAt($value)
 */
	class SubscriptionPlan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $company_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Driver|null $driver
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $company_id
 * @property int|null $assigned_driver_id
 * @property string $registration_number
 * @property string $qr_code_value
 * @property string|null $make
 * @property string|null $model
 * @property int|null $year
 * @property string|null $vehicle_type
 * @property string|null $engine_number
 * @property string|null $chassis_number
 * @property string|null $fuel_type
 * @property numeric|null $tank_capacity_litres
 * @property numeric $current_odometer
 * @property numeric|null $avg_kmpl_cached
 * @property string $status
 * @property numeric|null $last_lat
 * @property numeric|null $last_lng
 * @property \Illuminate\Support\Carbon|null $last_location_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Journey> $activeJourney
 * @property-read int|null $active_journey_count
 * @property-read \App\Models\Driver|null $assignedDriver
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VehicleDocument> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FuelEntry> $fuelEntries
 * @property-read int|null $fuel_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Journey> $journeys
 * @property-read int|null $journeys_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaintenanceRecord> $maintenanceRecords
 * @property-read int|null $maintenance_records_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereAssignedDriverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereAvgKmplCached($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereChassisNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereCurrentOdometer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereEngineNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereFuelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereLastLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereLastLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereLastLocationAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereMake($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereQrCodeValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereTankCapacityLitres($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereVehicleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehicle withoutTrashed()
 */
	class Vehicle extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $company_id
 * @property int $vehicle_id
 * @property string $document_type
 * @property string|null $document_number
 * @property \Illuminate\Support\Carbon|null $issue_date
 * @property \Illuminate\Support\Carbon|null $expiry_date
 * @property string|null $file_path
 * @property \Illuminate\Support\Carbon|null $reminder_sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Vehicle|null $vehicle
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument whereDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument whereReminderSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleDocument whereVehicleId($value)
 */
	class VehicleDocument extends \Eloquent {}
}

