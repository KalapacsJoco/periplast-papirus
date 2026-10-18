<footer class="mx-auto max-w-7xl px-4 py-3 sm:px-6">
    <div class="flex items-center justify-center gap-3 border-t border-slate-200 pt-3">
        <img src="{{ asset('kalasoft-logo.jpg') }}" alt="KalaSoft - Soft as a hammer" width="704" height="384" loading="lazy" class="h-auto w-24 rounded-md sm:w-28">
        <div class="text-xs text-slate-400">&copy; {{ now()->year }} KalaSoft<br>{{ $this->t('developed_by') }}: Kalapács József</div>
    </div>
</footer>
