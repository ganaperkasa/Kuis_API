<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DashboardAdminController extends Controller
{
    public function getdata()
    {
        // Total users
        $totalUsers = User::count();

        // Total reservations
        $totalReservations = Reservation::count();

        // User growth percentage (contoh: bulan ini vs bulan lalu)
        $lastMonthUsers = User::where('created_at', '>=', Carbon::now()->subMonth())->count();
        $previousMonthUsers = User::whereBetween('created_at',
            [Carbon::now()->subMonths(2), Carbon::now()->subMonth()]
        )->count();

        $userGrowth = $previousMonthUsers > 0
            ? (($lastMonthUsers - $previousMonthUsers) / $previousMonthUsers) * 100
            : 0;

        // Reservation change percentage
        $lastMonthReservations = Reservation::where('created_at', '>=', Carbon::now()->subMonth())->count();
        $previousMonthReservations = Reservation::whereBetween('created_at',
            [Carbon::now()->subMonths(2), Carbon::now()->subMonth()]
        )->count();

        $reservationChange = $previousMonthReservations > 0
            ? (($lastMonthReservations - $previousMonthReservations) / $previousMonthReservations) * 100
            : 0;

        return response()->json([
            'total_users' => $totalUsers,
            'total_reservations' => $totalReservations,
            'user_growth' => round($userGrowth, 2),
            'reservation_change' => round($reservationChange, 2)
        ]);
    }
    // public function monthlyConfirmedReservations()
    // {
    //     $currentYear = Carbon::now()->year;
    //     $monthlyData = [];

    //     for ($month = 1; $month <= 12; $month++) {
    //         $count = Reservation::where('status', 'confirmed')
    //             ->whereYear('created_at', $currentYear)
    //             ->whereMonth('created_at', $month)
    //             ->count();

    //         $monthlyData[] = $count;
    //     }

    //     return response()->json([
    //         'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    //         'data' => $monthlyData
    //     ]);
    // }
    public function monthlyConfirmedReservations()
    {
        try {
            $currentYear = Carbon::now()->year;
            $monthlyData = [];

            // Loop untuk setiap bulan dalam tahun ini
            for ($month = 1; $month <= 12; $month++) {
                $count = Reservation::where('status', 'confirmed')
                    ->whereYear('created_at', $currentYear)
                    ->whereMonth('created_at', $month)
                    ->count();

                $monthlyData[] = (int) $count; // Pastikan ini integer
            }

            // Log untuk debugging
            Log::info('Monthly reservation data: ', $monthlyData);

            return response()->json([
                'success' => true,
                'year' => $currentYear,
                'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'data' => $monthlyData
            ]);

        } catch (\Exception $e) {
            Log::error('Monthly reservations error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch monthly reservation data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'data' => array_fill(0, 12, 0) // Return zero data sebagai fallback
            ], 500);
        }
    }

    // Method tambahan untuk debugging
    public function debugReservations()
    {
        try {
            $currentYear = Carbon::now()->year;

            // Check total reservations
            $totalReservations = Reservation::count();
            $confirmedReservations = Reservation::where('status', 'confirmed')->count();
            $thisYearReservations = Reservation::whereYear('created_at', $currentYear)->count();

            // Check available statuses
            $statuses = Reservation::distinct('status')->pluck('status');

            // Sample reservations
            $sampleReservations = Reservation::select('id', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'debug_info' => [
                    'current_year' => $currentYear,
                    'total_reservations' => $totalReservations,
                    'confirmed_reservations' => $confirmedReservations,
                    'this_year_reservations' => $thisYearReservations,
                    'available_statuses' => $statuses,
                    'sample_reservations' => $sampleReservations
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
