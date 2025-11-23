<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sentinel Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold mb-8">🛡️ Sentinel Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm">Total Logs</h3>
                <p class="text-3xl font-bold">{{ $statistics['total_logs'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm">Today</h3>
                <p class="text-3xl font-bold">{{ $statistics['today_logs'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm">Critical</h3>
                <p class="text-3xl font-bold text-red-600">{{ $statistics['critical_logs'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm">Avg Response</h3>
                <p class="text-3xl font-bold">{{ round($statistics['average_response_time'], 2) }}ms</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Recent Logs</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left p-3">Type</th>
                            <th class="text-left p-3">Severity</th>
                            <th class="text-left p-3">Time</th>
                            <th class="text-left p-3">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLogs->take(20) as $log)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">{{ $log->type }}</td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs
                                    @if($log->severity === 'critical') bg-red-100 text-red-800
                                    @elseif($log->severity === 'warning') bg-yellow-100 text-yellow-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ $log->severity }}
                                </span>
                            </td>
                            <td class="p-3">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="p-3 text-sm">{{ json_encode($log->data) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
