
var $             = require('jquery');
var Sortable      = require('./_Sortable');
var LoadingButton = require('./_LoadingButton');
var Form          = require('./_Form');

function guidGenerator() {
    var S4 = function() {
        return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
    };
    return (S4()+S4());
}

/**
 * TemplateBuilder
 *
 * @param element
 * @constructor
 */
function TemplateBuilder(element)
{
    this.element    = element;
    this.url        = element.getAttribute('data-template-builder');
    this.menu       = element.querySelector('#template-menu');
    this.openButton = element.querySelector('#template-menu-button');
    this.locale     = element.getAttribute('data-locale');
    this.wasOpen    = false;
    this.open       = false;
    this.drag       = false;
    this.current    = null;

    var saveButton  = element.querySelector('#template-save-button');
    this.saveButton = new LoadingButton(saveButton, saveButton.getAttribute('data-loading-button'));

    // Open button
    this.openButton.addEventListener('click', function (event) {
        event.preventDefault();
        this.toggleMenu();
    }.bind(this));

    // Save button
    this.saveButton.element.addEventListener('click', function (event) {
        event.preventDefault();
        this.save();
    }.bind(this));

    // Blocks
    this.blockList = element.querySelector('#block-list');
    this.list(this.blockList, 'block-reference');

    // Objects
    this.objectList = element.querySelector('#object-list');
    this.list(this.objectList, 'object-reference');

    // Template
    this.templateContainer = element.querySelector('#template-container');
    this.sortable(this.templateContainer);

    // Init
    //this.init(this.element);
    this.init(this.templateContainer);
}

TemplateBuilder.prototype.init = function (element)
{
    [].forEach.call(element.querySelectorAll('.block'), function (block) {
        this.block(block);
    }.bind(this));

    [].forEach.call(element.querySelectorAll('.object'), function (object) {
        this.object(object);
    }.bind(this));
};

TemplateBuilder.prototype.toggleMenu = function (open)
{
    if (open !== undefined && this.open === open) {
        return;
    }

    this.menu.classList.toggle('slide-menu-container-open');
    this.openButton.querySelector('i').classList.toggle('glyphicon-chevron-left');
    this.openButton.querySelector('i').classList.toggle('glyphicon-chevron-right');
    this.open = !this.open;
};

TemplateBuilder.prototype.openMenu = function ()
{
    this.toggleMenu(true);
};

TemplateBuilder.prototype.closeMenu = function ()
{
    this.wasOpen = this.open;

    this.toggleMenu(false);
};

TemplateBuilder.prototype.list = function (element, name)
{
    new Sortable(element, {
        group: { name: name, pull: 'clone', put: false },
        sort: false,
        onStart: function () {
            this.closeMenu();
            this.drag = true;
        }.bind(this),
        onEnd: function () {
            this.openMenu();
            this.drag = false;
        }.bind(this)
    });
};

TemplateBuilder.prototype.sortable = function (element)
{
    new Sortable(element, {
        group: { name: 'block-list', pull: true, put: ['block-reference', 'object-reference', 'block-inner'] },
        handle: '.move-button',
        onStart: function () {
            this.closeMenu();
            this.drag = true;
        }.bind(this),
        onEnd: function () {
            if (this.wasOpen) {
                this.openMenu();
            }

            this.drag = false;
        }.bind(this),
        onAdd: function (event) {

            if (event.item.parentNode === event.from) {
                return;
            }

            if (event.from === this.blockList) {
                this.addBlock(event.item);
            }

            if (event.from === this.objectList) {
                this.addObject(event.item);
            }

        }.bind(this)
    });
};

TemplateBuilder.prototype.addBlock = function (element)
{
    // Dispatch DOM added element event
    document.dispatchEvent(new CustomEvent('dom.element.added', { 'detail': { 'element': element } }));

    // Enable block behavior
    this.block(element);
};

TemplateBuilder.prototype.addObject = function (element)
{
    // Dispatch DOM added element event
    document.dispatchEvent(new CustomEvent('dom.element.added', { 'detail': { 'element': element } }));

    // Enable object behavior
    this.object(element);

    // Open configure modal
    element.templateObject.openConfigureModal();
};

TemplateBuilder.prototype.block = function (element)
{
    // Create block
    element.templateBlock = new TemplateBlock(element, this);
};

TemplateBuilder.prototype.object = function (element)
{
    // Create object
    element.templateObject = new TemplateObject(element, this.locale);
};

TemplateBuilder.prototype.save = function ()
{
    this.saveButton.start();

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        var DONE = 4;
        var OK   = 200;

        if (xhr.readyState === DONE) {
            if (xhr.status === OK) {

            } else {
                alert('error');
            }

            this.saveButton.stop();
        }
    }.bind(this);
    xhr.open('POST', this.url);
    xhr.send(JSON.stringify(this.normalize(this.templateContainer)));
};

TemplateBuilder.prototype.inners = function (item)
{
    return [].map.call(this.children(item.querySelector('.block-inner').parentNode.parentNode), function (column) {
        return column.querySelector('.block-inner');
    });
};

TemplateBuilder.prototype.children = function (item)
{
    return [].filter.call(item.childNodes, function (child) {
        return child.nodeType === Node.ELEMENT_NODE;
    });
};

TemplateBuilder.prototype.normalize = function (item)
{
    var blockType  = item.getAttribute('data-block');
    var objectType = item.getAttribute('data-object');

    if (blockType !== null && blockType !== undefined) {
        return {
            component: 'block',
            type: blockType,
            config: [].map.call(this.inners(item), function (child) {
                return this.normalize(child);
            }.bind(this))
        }
    }

    if (objectType !== null && objectType !== undefined) {
        return item.templateObject.normalize();
    }

    var config = {};

    [].forEach.call(this.children(item), function (child) {
        var template = child.templateBlock || child.templateObject;
        config[template.uid] = this.normalize(child);
    }.bind(this));

    return config;
};

/**
 * Template Block
 *
 * @param element
 * @param builder
 * @constructor
 */
function TemplateBlock(element, builder)
{
    this.element = element;
    this.builder = builder;

    // UID
    this.uid = element.getAttribute('data-uid');

    if (this.uid === null || this.uid === undefined) {
        this.uid = guidGenerator();
        this.element.setAttribute('data-uid', this.uid);
    }

    // Init block inner as sortable target
    [].forEach.call(this.builder.inners(element), function (inner) {
        this.sortable(inner);
    }.bind(this));

    // Delete button behavior
    element.querySelector('.delete-button').addEventListener('click', function (event) {
        event.preventDefault();
        element.remove();
    });
}

TemplateBlock.prototype.sortable = function (element)
{
    new Sortable(element, {
        group: { name: 'block-list', pull: true, put: ['block-reference', 'object-reference', 'block-inner'] },
        handle: '.move-button',
        onStart: function () {
            this.builder.closeMenu();
            this.builder.drag = true;
        }.bind(this),
        onEnd: function () {
            if (this.builder.wasOpen) {
                this.builder.openMenu();
            }

            this.builder.drag = false;
        }.bind(this),
        onAdd: function (event) {

            if (event.item.parentNode === event.from) {
                return;
            }

            if (event.from === this.builder.blockList) {
                this.builder.addBlock(event.item);
            }

            if (event.from === this.builder.objectList) {
                this.builder.addObject(event.item);
            }

        }.bind(this)
    });
};

/**
 * Template Object
 *
 * @param element
 * @param locale
 * @constructor
 */
function TemplateObject(element, locale)
{
    this.element         = element;
    this.locale          = locale;
    this.configureModal  = element.querySelector('.configure-modal');
    this.saveButton      = this.configureModal.querySelector('.save-configuration');
    this.deleteButton    = element.querySelector('.delete-button');
    this.configureButton = element.querySelector('.configure-button');
    this.type            = element.getAttribute('data-object');

    // UID
    this.uid = element.getAttribute('data-uid');

    if (this.uid === null || this.uid === undefined) {
        this.uid = guidGenerator();
        this.element.setAttribute('data-uid', this.uid);
    }

    // Init modal
    $(this.configureModal).modal({show: false});

    // Buttons
    this.deleteButton.addEventListener('click', this.deleteButtonClicked.bind(this));
    this.configureButton.addEventListener('click', this.configureButtonClicked.bind(this));
    this.saveButton.addEventListener('click', this.saveButtonClicked.bind(this));

    // Object
    if (this.type === 'text') {
        this.object = new TextObject(this.element, this.locale);
    } else if (this.type === 'editable-text') {
        this.object = new EditableTextObject(this.element, this.locale);
    } else if (this.type === 'button-link') {
        this.object = new ButtonLinkObject(this.element, this.locale);
    } else if (this.type === 'participant') {
        this.object = new ParticipantObject(this.element, this.locale);
    } else if (this.type === 'choice') {
        this.object = new ChoiceObject(this.element, this.locale);
    } else if (this.type === 'image') {
        this.object = new ImageObject(this.element, this.locale);
    } else if (this.type === 'tag') {
        this.object = new TagObject(this.element, this.locale);
    } else if (this.type === 'collection') {
        this.object = new CollectionObject(this.element, this.locale);
    }

    this.object.fill();
}

TemplateObject.prototype.deleteButtonClicked = function (event)
{
    event.preventDefault();
    this.element.remove();
};

TemplateObject.prototype.configureButtonClicked = function (event)
{
    event.preventDefault();
    this.object.fill();
    this.openConfigureModal();
};

TemplateObject.prototype.saveButtonClicked = function (event)
{
    event.preventDefault();
    this.object.save();
    this.closeConfigureModal();
};

TemplateObject.prototype.openConfigureModal = function ()
{
    $(this.configureModal).modal('show');
};

TemplateObject.prototype.closeConfigureModal = function ()
{
    $(this.configureModal).modal('hide');
};

TemplateObject.prototype.getConfig = function ()
{
    return this.object.config;
};

TemplateObject.prototype.normalize = function ()
{
    return {
        component: 'object',
        type: this.type,
        config: this.object.config
    };
};

/**
 * TextObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function TextObject(element, locale)
{
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

TextObject.prototype.fill = function ()
{
    this.form.set('content', this.config.content);
    this.form.set('type', this.config.type);

    this.element.querySelector('[data-bind="content"]').innerHTML = '' + this.config.content[this.locale];
};

TextObject.prototype.save = function ()
{
    this.config.content = this.form.get('content');
    this.config.type    = this.form.get('type');

    this.element.querySelector('[data-bind="content"]').innerHTML = '' + this.config.content[this.locale];
};

/**
 * EditableTextObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function EditableTextObject(element, locale)
{
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

EditableTextObject.prototype.fill = function ()
{
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('placeholder', this.config.placeholder[this.locale]);
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('length', this.config.length);
    this.form.set('type', this.config.type);
    this.form.set('required', this.config.required);

    this.element.querySelector('[data-bind="label"]').innerHTML = '' + this.config.label[this.locale];
};

EditableTextObject.prototype.save = function ()
{
    this.config.label[this.locale]       = this.form.get('label');
    this.config.placeholder[this.locale] = this.form.get('placeholder');
    this.config.help[this.locale]        = this.form.get('help');
    this.config.length                   = this.form.get('length');
    this.config.type                     = this.form.get('type');
    this.config.required                 = this.form.get('required');

    this.element.querySelector('[data-bind="label"]').innerHTML = '' + this.config.label[this.locale];
};

/**
 * ButtonLinkObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function ButtonLinkObject(element, locale)
{
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

ButtonLinkObject.prototype.fill = function ()
{
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('required', this.config.required);

    this.element.querySelector('[data-bind="link"]').innerHTML = '' + this.config.label[this.locale];
};

ButtonLinkObject.prototype.save = function ()
{
    this.config.label[this.locale]    = this.form.get('label');
    this.config.help[this.locale]     = this.form.get('help');
    this.config.required              = this.form.get('required');

    this.element.querySelector('[data-bind="link"]').innerHTML = '' + this.config.label[this.locale];
};

/**
 * ParticipantObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function ParticipantObject(element, locale)
{
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

ParticipantObject.prototype.fill = function ()
{
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('numberOfParticipantShown', this.config.numberOfParticipantShown);

    this.element.querySelector('[data-bind="participant"]').innerHTML = '' + this.config.label[this.locale] + ' ' + this.config.numberOfParticipantShown;
};

ParticipantObject.prototype.save = function ()
{
    this.config.label[this.locale]       = this.form.get('label');
    this.config.numberOfParticipantShown = this.form.get('numberOfParticipantShown');

    this.element.querySelector('[data-bind="participant"]').innerHTML = '' + this.config.label[this.locale] + ' ' + this.config.numberOfParticipantShown;
};


/**
 * ChoiceObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function ChoiceObject(element, locale)
{
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

ChoiceObject.prototype.getContent = function ()
{
    var content = '';

    for (var key in this.config.choices) {
        if (Object.prototype.hasOwnProperty.call(this.config.choices, key)) {
            if (content !== '') {
                content = content + ',';
            }

            content = content + this.config.choices[key];
        }
    }

    return content;
};

ChoiceObject.prototype.fill = function ()
{
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('placeholder', this.config.placeholder[this.locale]);
    this.form.set('type', this.config.type);
    this.form.set('required', this.config.required);
    this.form.set('choices', this.getContent());

    this.element.querySelector('[data-bind="choice"]').innerHTML = '' + this.config.label[this.locale] + ' ' + this.config.type;
};

ChoiceObject.prototype.save = function ()
{
    this.config.label[this.locale]       = this.form.get('label');
    this.config.type[this.locale]        = this.form.get('type');
    this.config.placeholder[this.locale] = this.form.get('placeholder');
    this.config.required                 = this.form.get('required');

    var result = {};

    var current = this.form.get('choices').split(',').filter(function(item, pos, self) {
        return self.indexOf(item) === pos;
    });

    for (var index = 0; index < current.length; ++index) {
        result[current[index]] = current[index];
    }

    this.config.choices = result;

    this.element.querySelector('[data-bind="choice"]').innerHTML = '' + this.config.label[this.locale] + ' ' + this.config.type;
};

/**
 * ImageObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function ImageObject(element, locale)
{
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

ImageObject.prototype.fill = function ()
{
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('placeholder', this.config.placeholder[this.locale]);
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('required', this.config.required);

    this.element.querySelector('[data-bind="label"]').innerHTML = '' + this.config.label;
};

ImageObject.prototype.save = function ()
{
    this.config.label[this.locale]       = this.form.get('label');
    this.config.placeholder[this.locale] = this.form.get('placeholder');
    this.config.help[this.locale]        = this.form.get('help');
    this.config.required                 = this.form.get('required');

    this.element.querySelector('[data-bind="label"]').innerHTML = '' + this.config.label;
};

/**
 * TagObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function TagObject(element, locale)
{
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

TagObject.prototype.fill = function ()
{
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('tag', this.config.tag);

    this.element.querySelector('[data-bind="label"]').innerHTML = '' + this.config.label[this.locale];
};

TagObject.prototype.save = function ()
{
    this.config.label[this.locale] = this.form.get('label');
    this.config.tag                = this.form.get('tag');

    this.element.querySelector('[data-bind="label"]').innerHTML = '' + this.config.label[this.locale];
};

/**
 * CollectionObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function CollectionObject(element, locale)
{
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

CollectionObject.prototype.fill = function ()
{
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('placeholder', this.config.placeholder[this.locale]);
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('required', this.config.required);
    this.form.set('default', this.config.default);

    this.element.querySelector('[data-bind="label"]').innerHTML = '' + this.config.label[this.locale];
};

CollectionObject.prototype.save = function ()
{
    this.config.label[this.locale]       = this.form.get('label');
    this.config.placeholder[this.locale] = this.form.get('placeholder');
    this.config.help[this.locale]        = this.form.get('help');
    this.config.required                 = this.form.get('required');
    this.config.default                  = this.form.get('default');

    this.element.querySelector('[data-bind="label"]').innerHTML = '' + this.config.label[this.locale];
};

module.exports = TemplateBuilder;
