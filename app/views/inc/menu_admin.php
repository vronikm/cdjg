 <li class="nav-item">
                <a href="<?php echo APP_URL."dashboard/" ?>" class="nav-link <?php if ($url[0]=='dashboard') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>Dashboard</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="<?php echo APP_URL."representanteList/" ?>" class="nav-link <?php if ($url[0]=='representanteList') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon fas fa-diagnoses text-info"></i>
                  <p>Representantes</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="<?php echo APP_URL."alumnoList/" ?>" class="nav-link <?php if ($url[0]=='alumnoList') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon far fa-user-circle text-info"></i>
                  <p>Alumnos</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="<?php echo APP_URL."pagosList/" ?>" class="nav-link <?php if ($url[0]=='pagosList') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon far fa-money-bill-alt text-info"></i>
                  <p>Pagos</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="<?php echo APP_URL."carnetList/" ?>" class="nav-link <?php if ($url[0]=='carnetList') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon far fa-address-card text-info"></i>
                  <p>Carnets</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="<?php echo APP_URL."torneoList/" ?>" class="nav-link <?php if ($url[0]=='torneoList' || $url[0]=='equipoList' || $url[0]=='jugadorNew' || $url[0]=='jugadorLista') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon far fa-futbol text-info"></i>
                  <p>Torneos</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="<?php echo APP_URL."agenda/" ?>" class="nav-link <?php if ($url[0]=='agenda') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon fa fa-sticky-note text-info"></i>
                  <p>Agenda</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="<?php echo APP_URL."facturasList/" ?>" class="nav-link <?php if ($url[0]=='facturasList') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon fa fa-money-check text-info"></i>
                  <p>Facturas</p>
                </a>
              </li>

              <li class="nav-header">Asistencia alumno</li>
              <li class="nav-item <?php if ($url[0]=='asistencia' || $url[0]=='asistenciaAlumno' || $url[0]=='reporteAsistencia' || $url[0]=='buscarAsistencia' || $url[0]=='imprimirAsistencia') echo 'menu-open'; else echo ''; ?>">
                <a href="#" class="nav-link <?php if ($url[0]=='asistencia' || $url[0]=='asistenciaAlumno' || $url[0]=='reporteAsistencia') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon fas fa-clipboard-list"></i>
                  <p>Asistencia<i class="fas fa-angle-left right"></i></p>
                </a>
                <ul class="nav nav-treeview">

                  <li class="nav-item">
                    <a href="<?php echo APP_URL."asistencia/" ?>" class="nav-link <?php if ($url[0]=='asistencia' || $url[0]=='asistenciaAlumno') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Registrar asistencia</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."reporteAsistencia/" ?>" class="nav-link <?php if ($url[0]=='reporteAsistencia' || $url[0]=='buscarAsistencia') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Ver registro</p>
                    </a>
                  </li>
                </ul>
              </li>
              
              <li class="nav-header">Horarios</li>
              <li class="nav-item <?php if ($url[0]=='asistenciaHora' || $url[0]=='asistenciaLugar' || $url[0]=='asistenciaListHorario' || $url[0]=='asistenciaVerHorario' || $url[0]=='asistenciaHorario' || $url[0]=='asistenciaHorarioLista' || $url[0]=='asistenciaHorarioJugador') echo 'menu-open'; else echo ''; ?>">
                <a href="#" class="nav-link <?php if ($url[0]=='asistenciaHora' || $url[0]=='asistenciaLugar' || $url[0]=='asistenciaListHorario' || $url[0]=='asistenciaVerHorario' || $url[0]=='asistenciaHorario' || $url[0]=='asistenciaHorarioLista' || $url[0]=='asistenciaHorarioJugador') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon fas fa-clock"></i>
                  <p>Horarios<i class="fas fa-angle-left right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."asistenciaHora/" ?>" class="nav-link <?php if ($url[0]=='asistenciaHora') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Ingreso Horas</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."asistenciaLugar/" ?>" class="nav-link <?php if ($url[0]=='asistenciaLugar') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Lugar entrenamiento</p>
                    </a>
                  </li>

                  <li class="nav-item">
                    <a href="<?php echo APP_URL."asistenciaListHorario/" ?>" class="nav-link <?php if ($url[0]=='asistenciaListHorario' || $url[0]=='asistenciaVerHorario' || $url[0]=='asistenciaHorario' || $url[0]=='asistenciaHorarioLista' || $url[0]=='asistenciaHorarioJugador') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Horarios</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-header">Empleados</li>
              <li class="nav-item <?php if ($url[0]=='empleadoList' || $url[0]=='empleadoEntrada' || $url[0]=='empleadoAsistencias') echo 'menu-open'; else echo ''; ?>">  
              <a href="#" class="nav-link <?php if ($url[0]=='empleadoList' || $url[0]=='empleadoEntrada' || $url[0]=='empleadoAsistencias') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon far fa-address-card"></i>
                  <p>Empleados<i class="fas fa-angle-left right"></i></p>
                </a>
                <ul class="nav nav-treeview">    
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."empleadoList/" ?>" class="nav-link <?php if ($url[0]=='empleadoList' || $url[0]=='empleadoIE-view') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-file text-info"></i>
                      <p>Nómina</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."empleadoEntrada/" ?>" class="nav-link <?php if ($url[0]=='empleadoEntrada' ) echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-clock"></i>
                      <p>Registrar asistencia</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."empleadoAsistencias/" ?>" class="nav-link <?php if ($url[0]=='empleadoAsistencias') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-eye"></i>
                      <p>Reporte asistencias</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-header">Balance Mensual</li>
                <li class="nav-item <?php if ($url[0]=='ingresoList' || $url[0]=='egresoList' || $url[0]=='balanceResultados') echo 'menu-open'; else echo ''; ?>">
                  <a href="#" class="nav-link <?php if ($url[0]=='ingresoList' || $url[0]=='egresoList' || $url[0]=='balanceResultados') echo 'active'; else echo ''; ?>">
                    <i class="nav-icon fas fa-balance-scale"></i>
                    <p>Balance mensual<i class="fas fa-angle-left right"></i></p>
                  </a>
                  <ul class="nav nav-treeview">
                    <li class="nav-item">
                      <a href="<?php echo APP_URL."ingresoList/" ?>" class="nav-link <?php if ($url[0]=='ingresoList') echo 'active'; else echo ''; ?>">
                        <i class="nav-icon fas fa-chevron-right text-info"></i>
                        <p>Ingresos</p>
                      </a>
                    </li>
                  </ul>
                  <ul class="nav nav-treeview">
                    <li class="nav-item">
                      <a href="<?php echo APP_URL."egresoList/" ?>" class="nav-link <?php if ($url[0]=='egresoList') echo 'active'; else echo ''; ?>">
                        <i class="nav-icon fas fa-chevron-left text-info"></i>
                        <p>Egresos</p>
                      </a>
                    </li>
                  </ul>
                  <ul class="nav nav-treeview">
                    <li class="nav-item">
                      <a href="<?php echo APP_URL."balanceResultados/" ?>" class="nav-link <?php if ($url[0]=='balanceResultados') echo 'active'; else echo ''; ?>">
                        <i class="nav-icon far fa-circle text-info"></i>
                        <p>Resultados</p>
                      </a>
                    </li>
                  </ul>
                </li>

              <li class="nav-header">Cobranza</li>
              <li class="nav-item <?php if ($url[0]=='cobranzaPension' || $url[0]=='cobranzaPensionInactivos' || $url[0]=='cobranzaUniforme') echo 'menu-open'; else echo ''; ?>">
                <a href="#" class="nav-link <?php if ($url[0]=='cobranzaPension' || $url[0]=='cobranzaPensionInactivos' || $url[0]=='cobranzaUniforme') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon fas fa-book"></i>
                  <p>Gestión de cobranza<i class="fas fa-angle-left right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."cobranzaPension/" ?>" class="nav-link <?php if ($url[0]=='cobranzaPension') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Pensiones</p>
                    </a>
                  </li>
                </ul>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."cobranzaPensionInactivos/" ?>" class="nav-link <?php if ($url[0]=='cobranzaPensionInactivos') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Pensiones Inactivos</p>
                    </a>
                  </li>
                </ul>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."cobranzaUniforme/" ?>" class="nav-link <?php if ($url[0]=='cobranzaUniforme') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Uniformes</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-header">Reportes</li>
              <li class="nav-item <?php if ($url[0]=='reportePagosReceptados' || $url[0]=='reportePagos' || $url[0]=='reporteRubros' || $url[0]=='reportePagosReceptadosResumen' || $url[0]=='reportePensionAlumno') echo 'menu-open'; else echo ''; ?>">
                <a href="#" class="nav-link <?php if ($url[0]=='reportePagosReceptados' || $url[0]=='reportePagos' || $url[0]=='reporteRubros' || $url[0]=='reportePagosReceptadosResumen' || $url[0]=='reportePensionAlumno') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon fas fa-th"></i>
                  <p>Reportes<i class="fas fa-angle-left right"></i></p>
                </a>

                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."reportePagosReceptados/" ?>" class="nav-link <?php if ($url[0]=='reportePagosReceptados') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Pagos Receptados</p>
                    </a>
                  </li>
                </ul>

                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."reportePagos/" ?>" class="nav-link <?php if ($url[0]=='reportePagos') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Consolidado pagos</p>
                    </a>
                  </li>
                </ul>

                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."reporteRubros/" ?>" class="nav-link <?php if ($url[0]=='reporteRubros') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Alumnos por rubro</p>
                    </a>
                  </li>
                </ul>

                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."reportePagosReceptadosResumen/" ?>" class="nav-link <?php if ($url[0]=='reportePagosReceptadosResumen') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Resumen pagos</p>
                    </a>
                  </li>
                </ul>

                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."reportePensionAlumno/" ?>" class="nav-link <?php if ($url[0]=='reportePensionAlumno') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Pensión por alumno</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-header">Seguridad</li>
              <li class="nav-item <?php if ($url[0]=='userList' || $url[0]=='roList') echo 'menu-open'; else echo ''; ?>">
                <a href="#" class="nav-link <?php if ($url[0]=='userList' || $url[0]=='roList') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon fas fa-key"></i>
                  <p>Seguridad<i class="fas fa-angle-left right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href=<?php echo APP_URL."userList/";?> class="nav-link <?php if ($url[0]=='userList') echo 'active'; else echo ''; ?>" >
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Usuarios</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."roList/" ?>" class="nav-link <?php if ($url[0]=='roList') echo 'active'; else echo ''; ?>" >
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Roles</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."userMenu/" ?>" class="nav-link <?php if ($url[0]=='userMenu') echo 'active'; else echo ''; ?>" >
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Menú</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-header">Configuración</li>
              <li class="nav-item <?php if ($url[0]=='escuelaNew' || $url[0]=='sedeList' || $url[0]=='tablasNew' || $url[0]=='catalogosNew') echo 'menu-open'; else echo ''; ?>">
                <a href="#" class="nav-link <?php if ($url[0]=='escuelaNew' || $url[0]=='sedeList' || $url[0]=='tablasNew' || $url[0]=='catalogosNew') echo 'active'; else echo ''; ?>">
                  <i class="nav-icon far fa-edit"></i>
                  <p>Configuración<i class="fas fa-angle-left right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo APP_URL."escuelaNew/" ?>" class="nav-link <?php if ($url[0]=='escuelaNew') echo 'active'; else echo ''; ?>" >
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Club</p>
                    </a>
                  </li>

                  <li class="nav-item">
                    <a href="<?php echo APP_URL."sedeList/" ?>" class="nav-link <?php if ($url[0]=='sedeList') echo 'active'; else echo ''; ?>" >
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Sedes</p>
                    </a>
                  </li>

                  <li class="nav-item">
                    <a href="<?php echo APP_URL."tablasNew/" ?>" class="nav-link <?php if ($url[0]=='tablasNew') echo 'active'; else echo ''; ?>" >
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Tablas</p>
                    </a>
                  </li>

                  <li class="nav-item">
                    <a href="<?php echo APP_URL."catalogosNew/" ?>" class="nav-link <?php if ($url[0]=='catalogosNew') echo 'active'; else echo ''; ?>" >
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Catálogos</p>
                    </a>
                  </li>

                  <li class="nav-item">
                    <a href="<?php echo APP_URL."institucionList/" ?>" class="nav-link <?php if ($url[0]=='institucionList') echo 'active'; else echo ''; ?>" >
                      <i class="nav-icon far fa-circle text-info"></i>
                      <p>Institución</p>
                    </a>
                  </li>

                  <li class="nav-item">
                    <a href="<?php echo APP_URL."carnetConf/" ?>" class="nav-link <?php if ($url[0]=='carnetConf') echo 'active'; else echo ''; ?>">
                      <i class="nav-icon far fa-address-card text-info"></i>
                      <p>Carnets</p>
                    </a>
                  </li>

                </ul>
              </li>

              <li class="nav-header">Salir</li>
              <li class="nav-item">
                <a href=<?php echo APP_URL."logOut/";?> class="nav-link" id="btn_exit">
                  <i class="nav-icon far fa-circle text-danger"></i>
                  <p class="text">Salir</p>
                </a>
              </li>
            