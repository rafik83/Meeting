function normalizeTemplate(builder, item) {
  var blockType  = item.getAttribute('data-block');
  var objectType = item.getAttribute('data-object');

  if (blockType !== null && blockType !== undefined) {
    return {
      component: 'block',
      type: blockType,
      children: [].map.call(builder.inners(item), function (child) {
        return normalizeTemplate(builder, child);
      }),
      config: item.templateBlock.config
    }
  }

  if (objectType !== null && objectType !== undefined) {
    return item.templateObject.normalize();
  }

  var config = {};

  [].forEach.call(builder.children(item), function (child) {
    var template = child.templateBlock || child.templateObject;

    if (template !== undefined) {
      config[template.uid] = normalizeTemplate(builder, child);
    }
  });

  return config;
}

export default normalizeTemplate;
