<form method="post" enctype="multipart/form-data" class="form" data-validate novalidate>
    <?= csrf_field() ?>

    <label for="title">Headline *</label>
    <input id="title" name="title" required minlength="10" maxlength="255" data-counter
           value="<?= e($values['title']) ?>" placeholder="A clear, short headline">
    <?php if (isset($errors['title'])): ?><small class="field-error"><?= e($errors['title']) ?></small><?php endif; ?>

    <label for="category_id">Category *</label>
    <select id="category_id" name="category_id" required>
        <option value="">Choose a category</option>
        <?php foreach (get_categories($pdo) as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>" <?= (int) $values['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['category_id'])): ?><small class="field-error"><?= e($errors['category_id']) ?></small><?php endif; ?>

    <label for="content">Article *</label>
    <textarea id="content" name="content" rows="12" required minlength="50" maxlength="20000"
              placeholder="Write your article. Minimum 50 characters."><?= e($values['content']) ?></textarea>
    <?php if (isset($errors['content'])): ?><small class="field-error"><?= e($errors['content']) ?></small><?php endif; ?>

    <label for="image">Cover image (optional, max 2 MB)</label>
    <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,.gif" data-preview="img-preview">
    <img id="img-preview" class="preview-img" src="<?= e(imageSrc($values['image_url'] ?? '')) ?>" alt=""
         <?= empty($values['image_url']) ? 'hidden' : '' ?>>
    <?php if (isset($errors['image'])): ?><small class="field-error"><?= e($errors['image']) ?></small><?php endif; ?>

    <button type="submit" class="btn btn-primary btn-block"><?= e($submitLabel) ?></button>
</form>
