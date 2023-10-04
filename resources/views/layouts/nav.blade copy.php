<nav class="navbar navbar-vertical navbar-expand-lg" style="top: 2rem; background-color: #ededec; height: 100vh; border-right:0;">
    <script>
      var navbarStyle = window.config.config.phoenixNavbarStyle;
      if (navbarStyle && navbarStyle !== 'transparent') {
        document.querySelector('body').classList.add(`navbar-${navbarStyle}`);
      }
    </script>
    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
      <!-- scrollbar removed-->
      <div class="navbar-vertical-content">
        <ul class="navbar-nav flex-column" id="navbarVerticalNav">
          <li class="nav-item">
            <!-- label-->
            <p class="navbar-vertical-label" style="margin-top:0.5rem;">Application
            </p>
            <hr class="navbar-vertical-line" />

            <!-- parent pages-->
            <div class="nav-item-wrapper @yield('dashboard')"><a class="nav-link label-1 @yield('dashboard')" href="{{ route('home') }}" role="button" data-bs-toggle="" aria-expanded="false">
                <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="home"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Dashboard</span></span>
                </div>
              </a>
            </div>

            <!-- parent pages-->
            <div class="nav-item-wrapper @yield('consultation')"><a class="nav-link label-1 @yield('consultation')" href="{{ route('app.consultation') }}" role="button" data-bs-toggle="" aria-expanded="false">
                <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="user-plus"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Consultations</span></span>
                </div>
              </a>
            </div>

            <!-- parent pages-->
            <div class="nav-item-wrapper @yield('dispensation')"><a class="nav-link label-1 @yield('dispensation')" href="{{ route('esoins.dispensation') }}" role="button" data-bs-toggle="" aria-expanded="false">
                <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="edit-3"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Ordonnance</span></span>
                </div>
              </a>
            </div>
          </li>
          <div class="form m-4 p-2 bg-white rounded-2" style="border: 1px solid #ccc;">
            <select class="form-select" id="id_drs" data-choices="data-choices" data-options='{"removeItemButton":true,"placeholder":true}' onchange="changeValue('id_drs', 'id_district', 'structure');">
                <option value="">Select DRS</option>
                @foreach (get_structure(env('LEVEL_DRS')) as $structure)
                    <option value="{{ $structure->id }}">{{ $structure->nom_structure }}</option>
                @endforeach
            </select>
            <select class="form-select" id="id_district" name="id_district" data-options='{"removeItemButton":true,"placeholder":true}' onchange="changeValue('id_district', 'id_csps', 'structure');">
                <option value="">Select District</option>
            </select>
            <select class="form-select mt-2" id="id_csps" name="id_csps">
                <option value="">Select CSPS</option>
            </select>
          </div>
          <div class="form m-4 mt-0 p-2 bg-white rounded-2" style="border: 1px solid #ccc;">
            <div class="mb-2">
                <input class="form-control form-control-sm" id="sizingInputSm" type="date" placeholder=".form-control-sm" />
              </div>
              <div class="mb-0">
                <input class="form-control form-control-sm" id="sizingInputSm" type="date" placeholder=".form-control-sm" />
              </div>
          </div>
          <div class="d-grid gap-2 ms-4 me-4">
            <button class="btn btn-outline-primary btn-block btn-sm"><span data-feather="filter" class="me-1" style="font-size: 12px"></span>Filtrer</button>
          </div>
          <li class="nav-item">
            <!-- label-->
            <p class="navbar-vertical-label">Administration
            </p>
            <hr class="navbar-vertical-line" />
            <!-- parent pages-->

            <div class="nav-item-wrapper @yield('structure')"><a class="nav-link label-1 @yield('structure')" href="{{ route('structures.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="grid"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Structures</span></span>
                </div>
              </a>
            </div>

            <!-- parent pages-->

            <div class="nav-item-wrapper @yield('user')"><a class="nav-link label-1 @yield('user')" href="{{ route('users.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="user"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Users</span></span>
                </div>
              </a>
            </div>
            <div class="nav-item-wrapper @yield('role')"><a class="nav-link label-1 @yield('role')" href="{{ route('roles.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="users"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Rôles</span></span>
                </div>
              </a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>
