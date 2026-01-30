<?php

namespace Tests\Unit\Services;

use App\Services\TimeOfDayCalculator;
use Tests\TestCase;

class TimeOfDayCalculatorTest extends TestCase
{
    public function test_returns_null_when_time_is_null(): void
    {
        $result = TimeOfDayCalculator::calculate(null, '2024-06-15', 39.7392, -104.9903);

        $this->assertNull($result);
    }

    public function test_returns_null_when_time_is_empty(): void
    {
        $result = TimeOfDayCalculator::calculate('', '2024-06-15', 39.7392, -104.9903);

        $this->assertNull($result);
    }

    // Tests without coordinates (fallback times)
    public function test_dawn_without_coordinates(): void
    {
        $result = TimeOfDayCalculator::calculate('05:30', '2024-06-15', null, null);

        $this->assertEquals('Dawn', $result);
    }

    public function test_morning_without_coordinates(): void
    {
        $result = TimeOfDayCalculator::calculate('09:00', '2024-06-15', null, null);

        $this->assertEquals('Morning', $result);
    }

    public function test_midday_without_coordinates(): void
    {
        $result = TimeOfDayCalculator::calculate('12:00', '2024-06-15', null, null);

        $this->assertEquals('Midday', $result);
    }

    public function test_afternoon_without_coordinates(): void
    {
        $result = TimeOfDayCalculator::calculate('15:00', '2024-06-15', null, null);

        $this->assertEquals('Afternoon', $result);
    }

    public function test_dusk_without_coordinates(): void
    {
        $result = TimeOfDayCalculator::calculate('19:00', '2024-06-15', null, null);

        $this->assertEquals('Dusk', $result);
    }

    public function test_night_without_coordinates(): void
    {
        $result = TimeOfDayCalculator::calculate('22:00', '2024-06-15', null, null);

        $this->assertEquals('Night', $result);
    }

    public function test_early_morning_night_without_coordinates(): void
    {
        $result = TimeOfDayCalculator::calculate('03:00', '2024-06-15', null, null);

        $this->assertEquals('Night', $result);
    }

    // Tests with coordinates (Denver, CO)
    public function test_morning_with_coordinates(): void
    {
        // 9 AM in Denver on June 15 should be Morning (well after sunrise)
        $result = TimeOfDayCalculator::calculate('09:00', '2024-06-15', 39.7392, -104.9903);

        $this->assertEquals('Morning', $result);
    }

    public function test_midday_with_coordinates(): void
    {
        // 12 PM should be Midday
        $result = TimeOfDayCalculator::calculate('12:00', '2024-06-15', 39.7392, -104.9903);

        $this->assertEquals('Midday', $result);
    }

    public function test_afternoon_with_coordinates(): void
    {
        // 3 PM should be Afternoon
        $result = TimeOfDayCalculator::calculate('15:00', '2024-06-15', 39.7392, -104.9903);

        $this->assertEquals('Afternoon', $result);
    }

    public function test_night_with_coordinates(): void
    {
        // 11 PM should be Night
        $result = TimeOfDayCalculator::calculate('23:00', '2024-06-15', 39.7392, -104.9903);

        $this->assertEquals('Night', $result);
    }

    // Test with Carbon date object
    public function test_accepts_carbon_date(): void
    {
        $date = now()->setDate(2024, 6, 15);
        $result = TimeOfDayCalculator::calculate('12:00', $date, null, null);

        $this->assertEquals('Midday', $result);
    }

    // Test different timezone regions
    public function test_eastern_timezone_location(): void
    {
        // New York coordinates
        $result = TimeOfDayCalculator::calculate('12:00', '2024-06-15', 40.7128, -74.0060);

        $this->assertEquals('Midday', $result);
    }

    public function test_pacific_timezone_location(): void
    {
        // Los Angeles coordinates
        $result = TimeOfDayCalculator::calculate('12:00', '2024-06-15', 34.0522, -118.2437);

        $this->assertEquals('Midday', $result);
    }

    public function test_central_timezone_location(): void
    {
        // Chicago coordinates
        $result = TimeOfDayCalculator::calculate('12:00', '2024-06-15', 41.8781, -87.6298);

        $this->assertEquals('Midday', $result);
    }

    // Edge cases
    public function test_boundary_between_morning_and_midday(): void
    {
        // 11:00 AM is the boundary - should be Midday
        $result = TimeOfDayCalculator::calculate('11:00', '2024-06-15', null, null);

        $this->assertEquals('Midday', $result);
    }

    public function test_boundary_between_midday_and_afternoon(): void
    {
        // 2:00 PM is the boundary - should be Afternoon
        $result = TimeOfDayCalculator::calculate('14:00', '2024-06-15', null, null);

        $this->assertEquals('Afternoon', $result);
    }
}

