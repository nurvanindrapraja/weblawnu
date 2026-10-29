@forelse($logs as $log)
<tr class="hover:bg-gray-50">
    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
    <td class="px-4 py-3 text-sm text-gray-600">{{ $log->user?->name ?? 'System' }}</td>
    <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">{{ $log->module }}</span></td>
    <td class="px-4 py-3 text-sm text-gray-600 capitalize">{{ $log->action }}</td>
    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $log->description }}</td>
</tr>
@empty
<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada aktivitas.</td></tr>
@endforelse
