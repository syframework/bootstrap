<?php if (isset($LABEL)): ?>
<?php if ($LABEL_POSITION === 'before'): ?>
<label<?php if (isset($ID)) : ?> for="<?php echo $ID ?>"<?php endif ?><?php if (isset($LABEL_CLASS)) : ?> class="<?php echo $LABEL_CLASS ?>"<?php endif ?>><?php echo $LABEL ?></label>
<?php elseif ($LABEL_POSITION === 'wrap-before'): ?>
<label<?php if (isset($ID)) : ?> for="<?php echo $ID ?>"<?php endif ?><?php if (isset($LABEL_CLASS)) : ?> class="<?php echo $LABEL_CLASS ?>"<?php endif ?>>
<?php echo $LABEL ?>
<?php elseif ($LABEL_POSITION === 'wrap-after'): ?>
<label<?php if (isset($ID)) : ?> for="<?php echo $ID ?>"<?php endif ?><?php if (isset($LABEL_CLASS)) : ?> class="<?php echo $LABEL_CLASS ?>"<?php endif ?>>
<?php endif ?>
<?php endif ?>
<?php if (isset($ERROR) and $ERROR_POSITION === 'before'): ?>
<span class="<?php echo $ERROR_CLASS ?>"><?php echo $ERROR ?></span>
<?php endif ?>
<textarea<?php if (isset($BLOCK_ATTRIBUTES)): foreach ($BLOCK_ATTRIBUTES as $a): ?> <?php echo $a['NAME'] ?>="<?php echo $a['VALUE'] ?>"<?php endforeach; endif ?>><?php if (isset($BLOCK_CONTENT)): foreach ($BLOCK_CONTENT as $c): echo $c['ELEMENT']; endforeach; endif; ?></textarea>
<pre id="<?php echo $CODE_AREA_ID ?>"></pre>
<?php if (isset($LABEL)): ?>
<?php if ($LABEL_POSITION === 'after'): ?>
<label<?php if (isset($ID)) : ?> for="<?php echo $ID ?>"<?php endif ?>><?php echo $LABEL ?></label>
<?php elseif ($LABEL_POSITION === 'wrap-after'): ?>
<?php echo $LABEL ?>
</label>
<?php elseif ($LABEL_POSITION === 'wrap-before'): ?>
</label>
<?php endif ?>
<?php endif ?>
<?php if (isset($ERROR) and $ERROR_POSITION === 'after'): ?>
<span class="<?php echo $ERROR_CLASS ?>"><?php echo $ERROR ?></span>
<?php endif ?>