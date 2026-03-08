<?php
declare(strict_types=1);

/**
 * Escapa cadenas para mostrarlas en HTML.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Renderiza una lista de acciones para una tarjeta.
 * Cada acción se marca con data-attributes para la simulación en el front.
 */
function renderCardActions(array $actions, string $sectionId, string $sectionTitle): string
{
    ob_start();
    ?>
    <ul class="actions">
      <?php foreach ($actions as $action): ?>
        <li data-section="<?php echo e($sectionId); ?>" data-section-title="<?php echo e($sectionTitle); ?>" data-label="<?php echo e($action['label']); ?>">
          <div class="action-text">
            <strong><?php echo e($action['label']); ?></strong>
            <?php if (!empty($action['meta'])): ?>
              <small><?php echo e($action['meta']); ?></small>
            <?php endif; ?>
          </div>
          <?php if (!empty($action['tag'])): ?>
            <span class="tag"><?php echo e($action['tag']); ?></span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php
    return trim((string)ob_get_clean());
}
