

<?php $__env->startSection('title', $challenge->name . ' - Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="challenge-show-header">
    <div class="header-left">
        <div class="challenge-info-row">
            <h1 class="challenge-title"><?php echo e($challenge->name); ?></h1>
            <div class="challenge-meta">
                <span class="code-badge">
                    <span class="code-label">Código:</span>
                    <span class="code-value" id="challengeCode"><?php echo e($challenge->join_code); ?></span>
                    <button type="button" class="code-copy-btn whatsapp-btn" onclick="shareOnWhatsApp()" title="Compartir en WhatsApp">
                        <svg class="whatsapp-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </button>
                </span>
                <?php if($challenge->is_active): ?>
                    <span class="status-badge active">
                        <span class="status-dot">●</span>
                        <span class="status-text">Activo</span>
                    </span>
                <?php else: ?>
                    <span class="status-badge inactive">
                        <span class="status-dot">○</span>
                        <span class="status-text">Inactivo</span>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="challenge-controls">
        <?php if(Auth::user()->isDocente()): ?>
            <div class="timer-actions">
                <div class="timer-display" id="timer">00:00:00</div>
                
                <?php if($challenge->is_active): ?>
                    <?php if(!$challenge->started_at || $challenge->paused_at): ?>
                        <form action="<?php echo e(route('challenge.start', $challenge)); ?>" method="POST" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-sm btn-success" title="<?php echo e($challenge->started_at ? 'Reanudar' : 'Iniciar'); ?>">
                                ▶️
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="<?php echo e(route('challenge.pause', $challenge)); ?>" method="POST" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-sm btn-warning" title="Pausar">
                                ⏸️
                            </button>
                        </form>
                    <?php endif; ?>

                    <form action="<?php echo e(route('challenge.finalize', $challenge)); ?>" method="POST" onsubmit="return confirm('¿Estás seguro de finalizar este desafío?')" style="display:inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-danger" title="Finalizar">
                            ⏹️
                        </button>
                    </form>
                <?php else: ?>
                    <form action="<?php echo e(route('challenge.resume', $challenge)); ?>" method="POST" style="display:inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-primary" title="Reactivar">
                            🔄
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if(Auth::user()->isDocente()): ?>
                    <button type="button" class="btn btn-sm btn-primary" onclick="openAddStudentModal()" title="Añadir Estudiante">
                        ➕
                    </button>
                <?php endif; ?>

                <?php if($challenge->hasTeams()): ?>
                    <form action="<?php echo e(route('challenge.teams.delete', $challenge)); ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Deshacer los equipos y volver al modo individual?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-sm btn-danger" title="Deshacer Equipos">
                            👥🚫
                        </button>
                    </form>
                <?php else: ?>
                    <button type="button" class="btn btn-sm btn-info" onclick="openTeamsModal()" title="Formar Equipos">
                        👥
                    </button>
                <?php endif; ?>
                
                <button type="button" class="btn btn-sm btn-info" onclick="openRouletteModal()" title="Ruleta">
                    🎰
                </button>
            </div>
        <?php else: ?>
            <div class="timer-display" id="timer">00:00:00</div>
        <?php endif; ?>

        <?php if(Auth::user()->isEstudiante() && $challenge->is_active): ?>
            <?php
                $myParticipant = $participants->where('user_id', Auth::id())->first();
            ?>
            <?php if($myParticipant && !$myParticipant->finished_at): ?>
                <form action="<?php echo e(route('challenge.submit', $challenge)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-sm btn-primary">¡Terminé!</button>
                </form>
            <?php elseif($myParticipant && $myParticipant->finished_at): ?>
                <div class="alert-compact">
                    ✓ <?php echo e(gmdate("H:i:s", $myParticipant->duration_seconds)); ?>

                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="participants-grid">
    <?php if($challenge->hasTeams()): ?>
        <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="participant-card team-card" style="border-top: 4px solid <?php echo e($team->color); ?>;">
                <div class="card-header">
                    <div class="team-name" style="color: <?php echo e($team->color); ?>; font-weight: bold;"><?php echo e($team->name); ?></div>
                </div>
                
                <div class="team-members-list">
                    <?php $__currentLoopData = $team->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="team-member-item">
                            <span class="member-name"><?php echo e($member->user->name); ?></span>
                            <?php if($member->finished_at): ?>
                                <span class="member-status">✓</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="points-display">
                    <span class="points-value"><?php echo e($team->total_points); ?></span>
                    <span class="points-label">pts equipo</span>
                </div>
                
                <?php if($team->average_time): ?>
                    <div class="submission-time">⏱ Prom: <?php echo e(gmdate("H:i:s", $team->average_time)); ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <?php $__currentLoopData = $participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $participant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div id="participant-card-<?php echo e($participant->id); ?>" class="participant-card <?php echo e($index < 3 ? 'top-rank rank-' . ($index + 1) : ''); ?> <?php echo e($participant->user_id === Auth::id() ? 'current-user' : ''); ?>">
                <div class="card-header">
                    <div class="position-badge">
                        <?php if($index === 0): ?>
                            <span class="medal">🥇</span>
                        <?php elseif($index === 1): ?>
                            <span class="medal">🥈</span>
                        <?php elseif($index === 2): ?>
                            <span class="medal">🥉</span>
                        <?php else: ?>
                            <span class="rank-number">#<?php echo e($index + 1); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="student-info">
                    <?php
                        $words = explode(' ', trim($participant->user->name));
                    ?>
                    <div class="student-name" lang="es">
                        <?php $__currentLoopData = $words; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $word): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="<?php echo e($i >= 2 ? 'student-name-surname' : 'student-name-given'); ?>"><?php echo e($word); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php if($participant->finished_at): ?>
                        <div class="submission-time">⏱ <?php echo e(gmdate("H:i:s", $participant->duration_seconds)); ?></div>
                    <?php endif; ?>
                </div>

                <div class="points-display">
                    <span class="points-value"><?php echo e($participant->points); ?></span>
                    <span class="points-label">pts</span>
                </div>

                <?php if(Auth::user()->isDocente() && $challenge->is_active): ?>
                    <div class="card-actions">
                        <button type="button" class="btn-icon settings" onclick="openScoreModal(<?php echo e($participant->id); ?>, '<?php echo e($participant->user->name); ?>', <?php echo e($participant->points); ?>, <?php echo e($participant->duration_seconds ?? 0); ?>, <?php echo e($participant->finished_at ? 'true' : 'false'); ?>)" title="Ajustar Estudiante">
                            ⚙️
                        </button>
                        <button type="button" class="btn-icon validate <?php echo e($participant->finished_at ? 'validated' : ''); ?>" onclick="openDeliveryModal(<?php echo e($participant->id); ?>, '<?php echo e($participant->user->name); ?>', <?php echo e($participant->finished_at ? 'true' : 'false'); ?>)" title="<?php echo e($participant->finished_at ? 'Devolver Trabajo' : 'Entregar Trabajo'); ?>">
                            <?php echo e($participant->finished_at ? '🔄' : '⏰'); ?>

                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        
        <?php if($participants->isEmpty()): ?>
            <div class="empty-state-container" style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #64748b;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">👻</div>
                <h3>No hay estudiantes activos</h3>
                <p>Comparte el código <strong><?php echo e($challenge->join_code); ?></strong> o añade estudiantes manualmente.</p>
                <?php if(Auth::user()->isDocente()): ?>
                    <button type="button" class="btn btn-primary mt-3" onclick="openAddStudentModal()">
                        Añadir Estudiante Manualmente
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Score Adjustment Modal -->
<div id="scoreModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalStudentName" class="modal-title">⚙️ Ajustar Estudiante</h2>
            <span class="close" onclick="closeScoreModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="scoreForm" method="POST" class="score-form-modal">
                <?php echo csrf_field(); ?>
                
                <!-- Sección de Puntos -->
                <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                    <h3 style="font-size: 1rem; margin-bottom: 0.75rem; color: #6b7280; text-align: center;">📊 Puntos</h3>
                    <div id="suggestedPointsInfo" class="alert-compact-info" style="display:none; margin-bottom: 0.5rem;">
                        💡 Sugerencia: <strong id="suggestedPointsValue"></strong> puntos
                    </div>
                    <div class="text-center mb-2 small text-muted">
                        Rango: <?php echo e($challenge->min_points ?? 0); ?> - <?php echo e($challenge->max_points); ?> pts
                    </div>
                    <div class="score-controls">
                        <button type="button" class="btn-adjust minus" onclick="adjustModalScore(-1)">-1</button>
                        <button type="button" class="btn-adjust minus" onclick="adjustModalScore(-5)">-5</button>
                        
                        <input type="number" id="modalPointsInput" name="points" class="form-control text-center" min="0" max="<?php echo e($challenge->max_points); ?>" required>
                        
                        <button type="button" class="btn-adjust plus" onclick="adjustModalScore(1)">+1</button>
                        <button type="button" class="btn-adjust plus" onclick="adjustModalScore(5)">+5</button>
                    </div>
                    <div style="text-align: center; margin-top: 0.5rem;">
                        <button type="button" class="btn btn-sm btn-outline" onclick="applySuggestedPoints()">Usar Sugerencia</button>
                    </div>
                </div>

                <!-- Sección de Tiempo -->
                <div id="timeAdjustSection" style="display:none; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                    <h3 id="timeSectionTitle" style="font-size: 1rem; margin-bottom: 0.75rem; color: #6b7280; text-align: center;">⏱️ Tiempo de Actividad</h3>
                    <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <input type="number" id="modalMinutes" class="form-control text-center" style="width: 70px; font-size: 1.2rem;" min="0" placeholder="00">
                        <span style="font-weight: bold; font-size: 1.2rem;">:</span>
                        <input type="number" id="modalSeconds" class="form-control text-center" style="width: 70px; font-size: 1.2rem;" min="0" max="59" placeholder="00">
                    </div>
                    
                    <p class="small mb-2" style="text-align: center; color: #6b7280;">Ajustar Tiempo:</p>
                    <div class="penalty-controls" style="display: flex; justify-content: center; gap: 0.4rem; margin-bottom: 0.5rem;">
                        <button type="button" class="btn-penalty btn-penalty-minus" onclick="subtractModalTime(60)" title="Restar 60 segundos">-60s</button>
                        <button type="button" class="btn-penalty btn-penalty-minus" onclick="subtractModalTime(30)" title="Restar 30 segundos">-30s</button>
                        <button type="button" class="btn-penalty btn-penalty-minus" onclick="subtractModalTime(10)" title="Restar 10 segundos">-10s</button>
                        <span style="width:1px; background:#e5e7eb; margin: 0 0.2rem;"></span>
                        <button type="button" class="btn-penalty" onclick="addModalPenalty(10)" title="Sumar 10 segundos">+10s</button>
                        <button type="button" class="btn-penalty" onclick="addModalPenalty(30)" title="Sumar 30 segundos">+30s</button>
                        <button type="button" class="btn-penalty" onclick="addModalPenalty(60)" title="Sumar 60 segundos">+60s</button>
                    </div>
                    <input type="hidden" name="duration_seconds" id="modalDurationInput">
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeScoreModal()">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Guardar Cambios</button>
                </div>
            </form>
            
            <form id="deleteParticipantForm" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a este estudiante del desafío? Esta acción no se puede deshacer.')" style="margin-top: 1.5rem; border-top: 1px solid #eee; padding-top: 1rem; text-align: center;">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-sm btn-danger">
                    🗑️ Eliminar Estudiante
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Delivery Modal -->
<div id="deliveryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="deliveryModalStudentName" class="modal-title">Gestionar Entrega</h2>
            <span class="close" onclick="closeDeliveryModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="deliveryForm" method="POST" class="score-form-modal">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="submit_action" id="deliveryAction" value="submit">
                
                <div class="text-center" style="padding: 1.5rem 0;">
                    <div id="deliveryMessage" style="font-size: 1.1rem; margin-bottom: 1.5rem; color: #6b7280;">
                        <!-- Mensaje dinámico -->
                    </div>
                    <p class="small text-muted">
                        <strong>Entregar Trabajo:</strong> Detiene el cronómetro del estudiante y guarda el tiempo actual.<br>
                        <strong>Devolver Trabajo:</strong> Permite que el estudiante continúe trabajando.
                    </p>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeDeliveryModal()">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success" id="btnSubmitWork" style="display:none;">⏰ Entregar Trabajo</button>
                    <button type="button" class="btn btn-sm btn-warning" id="btnReturnWork" onclick="submitReturn()" style="display:none;">🔄 Devolver Trabajo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div id="addStudentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">➕ Añadir Estudiante</h2>
            <span class="close" onclick="closeAddStudentModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form action="<?php echo e(route('challenge.addStudent', $challenge)); ?>" method="POST" class="score-form-modal">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="studentSearchInput" class="form-label">Buscar Estudiante:</label>
                    <input type="text" id="studentSearchInput" class="form-control" placeholder="Escribe para buscar..." autocomplete="off">
                    
                    <select name="student_id" id="studentSelect" class="form-control" required size="5">
                        <?php $__currentLoopData = $availableStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($student->id); ?>"><?php echo e($student->name); ?> (<?php echo e($student->email); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div id="noStudentsFound" style="display:none; color: #6b7280; font-size: 0.9rem; margin-top: 0.5rem;">No se encontraron estudiantes</div>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeAddStudentModal()">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success">Añadir al Desafío</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Teams Modal -->
<div id="teamsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">👥 Formar Equipos</h2>
            <span class="close" onclick="closeTeamsModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p class="small mb-2 text-center">Selecciona el tamaño de los equipos:</p>
            <form action="<?php echo e(route('challenge.teams.create', $challenge)); ?>" method="POST" class="score-form-modal">
                <?php echo csrf_field(); ?>
                <div class="form-group text-center">
                    <div class="score-controls">
                        <button type="button" class="btn-adjust minus" onclick="adjustTeamSize(-1)">-</button>
                        <input type="number" id="teamSizeInput" name="team_size" class="form-control text-center" value="2" min="2" max="10" required style="width: 60px; font-size: 1.2rem; font-weight: bold;">
                        <button type="button" class="btn-adjust plus" onclick="adjustTeamSize(1)">+</button>
                    </div>
                    <small class="text-muted">Integrantes por equipo</small>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeTeamsModal()">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Formar Equipos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Roulette Modal -->
<div id="rouletteModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2 class="modal-title">🎰 Ruleta de Estudiantes</h2>
            <span class="close" onclick="closeRouletteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <!-- Step 1: Select Participants -->
            <div id="rouletteStep1" class="roulette-step">
                <h3>Selecciona los participantes:</h3>
                <div class="participants-selection">
                    <div class="selection-controls">
                        <button type="button" class="btn btn-sm btn-outline" onclick="selectAllParticipants()">Seleccionar Todos</button>
                        <button type="button" class="btn btn-sm btn-outline" onclick="deselectAllParticipants()">Deseleccionar Todos</button>
                    </div>
                    <div id="participantsList" class="participants-checkboxes"></div>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeRouletteModal()">Cancelar</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="spinRoulette()">� Girar Ruleta</button>
                </div>
            </div>

            <!-- Step 2: Spinning Animation -->
            <div id="rouletteStep2" class="roulette-step" style="display:none;">
                <div class="roulette-spinner">
                    <div class="spinner-display" id="spinnerDisplay">Girando...</div>
                </div>
            </div>

            <!-- Step 3: Winner & Points -->
            <div id="rouletteStep3" class="roulette-step" style="display:none;">
                <div class="roulette-winner">
                    <h2>🎉 Ganador:</h2>
                    <div class="winner-name" id="winnerName"></div>
                    <div class="winner-current-points" id="winnerCurrentPoints" style="margin-top: 0.5rem; font-size: 1.1rem; color: #6b7280;"></div>
                </div>
                <div class="points-assignment">
                    <h3>Asignar puntos:</h3>
                    <div class="points-buttons">
                        <button type="button" class="btn-points" onclick="assignRoulettePoints(1)">+1</button>
                        <button type="button" class="btn-points" onclick="assignRoulettePoints(5)">+5</button>
                        <button type="button" class="btn-points" onclick="assignRoulettePoints(10)">+10</button>
                    </div>
                    <div class="custom-points">
                        <input type="number" id="customRoulettePoints" class="form-control" min="1" placeholder="Puntos personalizados">
                        <button type="button" class="btn btn-sm btn-success" onclick="assignCustomRoulettePoints()">Asignar</button>
                    </div>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="closeRouletteModal()">Volver a Pizarra</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="resetRoulette()">🎰 Nueva Ruleta</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    window.ChallengeConfig = {
        challengeId: <?php echo e($challenge->id); ?>,
        isDocente: <?php echo e(Auth::user()->isDocente() ? 'true' : 'false'); ?>,
        userId: <?php echo e(Auth::id()); ?>,
        minPoints: <?php echo e($challenge->min_points ?? 0); ?>,
        maxPoints: <?php echo e($challenge->max_points ?? 100); ?>,
        seconds: <?php echo e($challenge->getCurrentTimeSeconds()); ?>,
        isRunning: <?php echo e($challenge->started_at && !$challenge->paused_at ? 'true' : 'false'); ?>

    };
</script>
<script src="<?php echo e(asset('js/challenge-show.js')); ?>?v=<?php echo e(time()); ?>"></script>
<link rel="stylesheet" href="<?php echo e(asset('css/challenge-show.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\classroomclash\resources\views/challenge/show.blade.php ENDPATH**/ ?>