<?php
/**
 * Componente: Modal Editar Usuario
 * Archivo: app/Views/administrador/partials/usuarios_modal_editar.php
 * 
 * Formulario modal para modificar credenciales, rol y especialidad técnica.
 * 
 * @var array $roles
 * @var array $especialidades
 */
?>
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border shadow rounded-3" style="border-color: #e2e8f0;">
            <div class="modal-header bg-light border-bottom py-3 px-4" style="border-color: #e2e8f0;">
                <h5 class="modal-title fw-bold text-dark fs-6" id="modalEditarUsuarioLabel">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="/administrador/usuarios/editar" method="POST">
                <?= csrfField(); ?>
                <input type="hidden" name="id_usuario" id="edit_usuario_id" value="">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_usuario_username" class="form-label text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Nombre de Usuario <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_usuario_username" name="username" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="edit_usuario_id_rol" class="form-label text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Rol Asignado <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_usuario_id_rol" name="id_rol" required>
                            <option value="">Seleccione un rol...</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int)$r['id_rol'] ?>" data-nombre-rol="<?= htmlspecialchars($r['nombre_rol'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($r['nombre_rol'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="grupo_edit_especialidad" style="display: none;">
                        <label for="edit_usuario_id_especialidad" class="form-label text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Especialidad Técnica (Operador)</label>
                        <select class="form-select" id="edit_usuario_id_especialidad" name="id_especialidad">
                            <option value="">Seleccione una especialidad...</option>
                            <?php foreach ($especialidades as $esp): ?>
                                <option value="<?= (int)$esp['id_especialidad'] ?>">
                                    <?= htmlspecialchars($esp['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small text-muted">Determina qué API de trabajos consultará el operador en terreno.</div>
                    </div>
                    <div class="mb-0">
                        <label for="edit_usuario_password" class="form-label text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Nueva Contraseña (Opcional)</label>
                        <input type="password" class="form-control" id="edit_usuario_password" name="password" minlength="6" autocomplete="new-password" placeholder="••••••••">
                        <div class="form-text small text-muted">Dejar en blanco para mantener la contraseña actual del usuario.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2.5 px-4" style="border-color: #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
