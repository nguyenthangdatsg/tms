<div class="dropdown">
    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
        @if ($currentLocale === 'vi')
            <img src="https://flagcdn.com/w20/vn.png" alt="VN" width="20" height="15">
        @else
            <img src="https://flagcdn.com/w20/gb.png" alt="EN" width="20" height="15">
        @endif
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item {{ $currentLocale === 'vi' ? 'active' : '' }}" 
               href="?lang=vi">
                <img src="https://flagcdn.com/w20/vn.png" alt="VN" width="20" height="15" class="me-2">
                Tiếng Việt
            </a>
        </li>
        <li>
            <a class="dropdown-item {{ $currentLocale === 'en' ? 'active' : '' }}" 
               href="?lang=en">
                <img src="https://flagcdn.com/w20/gb.png" alt="EN" width="20" height="15" class="me-2">
                English
            </a>
        </li>
    </ul>
</div>
