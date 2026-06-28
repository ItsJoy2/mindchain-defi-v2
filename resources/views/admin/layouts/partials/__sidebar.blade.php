    <div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
      <div class="sidebar-header border-bottom">
        <div class="sidebar-brand me-auto">
            <img src="{{ asset('assets/mindchsinwallet.png') }}" alt="Mindchain Logo" class="sidebar-brand-full" height="30">
        </div>
        <button class="btn-close d-lg-none" type="button" data-coreui-theme="dark" aria-label="Close" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()"></button>
      </div>
      <ul class="sidebar-nav" data-coreui="navigation" data-simplebar>
        <li class="nav-item">
          <a class="nav-link" href=" {{ route('admin.dashboard') }}">
            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
              <path fill="var(--ci-primary-color, currentcolor)" d="M425.706 142.294A240 240 0 0 0 16 312v88h144v-32H48v-56c0-114.691 93.309-208 208-208s208 93.309 208 208v56H352v32h144v-88a238.43 238.43 0 0 0-70.294-169.706" class="ci-primary" />
              <path fill="var(--ci-primary-color, currentcolor)" d="M80 264h32v32H80zm160-136h32v32h-32zm-104 40h32v32h-32zm264 96h32v32h-32zm-102.778 71.1 69.2-144.173-28.85-13.848-69.183 144.135a64.141 64.141 0 1 0 28.833 13.886M256 416a32 32 0 1 1 32-32 32.036 32.036 0 0 1-32 32" class="ci-primary" />
            </svg>
            Dashboard
            {{-- <span class="badge badge-sm bg-info ms-auto">NEW</span> --}}
          </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.users.index') }}">
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                    <path fill="var(--ci-primary-color, currentcolor)"
                        d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3zM528 256A112 112 0 1 0 528 32a112 112 0 1 0 0 224zm-16 48H480c-13.3 0-25.9 2.3-37.6 6.5c54.5 41.8 89.6 107.5 89.6 181.8c0 10.2-1.1 20.2-3.1 29.7H610.3c16.4 0 29.7-13.3 29.7-29.7C640 389.8 582.2 304 512 304z" />
                </svg>
                Users
            </a>
        </li>
        <li class="nav-group {{ request()->routeIs('admin.history.*') ? 'show' : '' }}">

            <a class="nav-link nav-group-toggle {{ request()->routeIs('admin.history.*') ? 'active' : '' }}"
            href="#">

                <i class="fas fa-history nav-icon"></i>

                Investment History
            </a>

            <ul class="nav-group-items">

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.history.angel-staking') ? 'active' : '' }}"
                    href="{{ route('admin.history.angel-staking') }}">

                        <span class="nav-icon"></span>

                        Angel Staking
                    </a>
                </li>

                {{-- Future History Pages --}}

                {{--
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.history.deposit') ? 'active' : '' }}"
                    href="{{ route('admin.history.deposit') }}">
                        <span class="nav-icon"></span>
                        Deposit History
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.history.withdraw') ? 'active' : '' }}"
                    href="{{ route('admin.history.withdraw') }}">
                        <span class="nav-icon"></span>
                        Withdraw History
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.history.transfer') ? 'active' : '' }}"
                    href="{{ route('admin.history.transfer') }}">
                        <span class="nav-icon"></span>
                        Transfer History
                    </a>
                </li>
                --}}

            </ul>

        </li>
        <li class="nav-group {{ request()->routeIs('admin.transactions.*') || request()->routeIs('admin.ambassador-history.*') ? 'show' : '' }}">

            <a class="nav-link nav-group-toggle" href="#">
                <i class="fas fa-exchange-alt nav-icon"></i>
                Transactions
            </a>

            <ul class="nav-group-items compact">

                <li class="nav-item">
                    <a
                        class="nav-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}"
                        href="{{ route('admin.transactions.index') }}"
                    >
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Transaction History
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link {{ request()->routeIs('admin.ambassador-history.*') ? 'active' : '' }}"
                        href="{{ route('admin.ambassador-history.index') }}"
                    >
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Ambassador History
                    </a>
                </li>

            </ul>

        </li>
        <li class="nav-group {{ request()->routeIs('admin.settings.*') ? 'show' : '' }}">

            <a class="nav-link nav-group-toggle" href="#">
                <i class="fas fa-cogs nav-icon"></i>
                Settings
            </a>

            <ul class="nav-group-items compact">

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}"
                        href="{{ route('admin.settings.index') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Investment Settings
                    </a>

                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.wallet-icons') ? 'active' : '' }}"
                        href="{{ route('admin.settings.wallet-icons') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Wallet Icons
                    </a>

                </li>

            </ul>

        </li>
      </ul>
      <div class="sidebar-footer border-top d-none d-md-flex">
        <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
      </div>
    </div>
