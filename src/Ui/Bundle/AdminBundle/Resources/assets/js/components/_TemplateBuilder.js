var $             = require('jquery');
var Sortable      = require('./_Sortable');
var LoadingButton = require('./_LoadingButton');
var Form          = require('./_Form');

function guidGenerator() {
    var S4 = function() {
        return 'M' + (((1+Math.random())*0x10000)|0).toString(16).substring(1);
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
        onStart: function (event) {
            this.closeMenu();
            this.drag = true;
            var uid = guidGenerator();
            event.item.innerHTML = event.item.innerHTML.replace(new RegExp('__UID__', 'g'), uid);
            event.item.setAttribute('data-uid', uid);
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
                var config = JSON.parse(xhr.response);
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
            children: [].map.call(this.inners(item), function (child) {
                return this.normalize(child);
            }.bind(this)),
            config: item.templateBlock.config
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
    this.element         = element;
    this.builder         = builder;
    this.config          = JSON.parse(this.element.getAttribute('data-config'));
    this.configureModal  = element.querySelector('.configure-modal');
    this.configureButton = element.querySelector('.configure-button');
    this.form            = new Form(this.configureModal);
    this.saveButton      = this.configureModal.querySelector('.save-configuration');

    // UID
    this.uid = element.getAttribute('data-uid');

    // Init modal
    $(this.configureModal).modal({show: false});

    // Init block inner as sortable target
    [].forEach.call(this.builder.inners(element), function (inner) {
        this.sortable(inner);
    }.bind(this));

    // Delete button behavior
    this.element.querySelector('.delete-button').addEventListener('click', function (event) {
        event.preventDefault();
        this.element.remove();
    }.bind(this));

    // Modal behavior
    this.configureButton.addEventListener('click', this.configureButtonClicked.bind(this));
    this.saveButton.addEventListener('click', this.saveButtonClicked.bind(this));
}

TemplateBlock.prototype.configureButtonClicked = function (event)
{
    event.preventDefault();
    this.fill();
    this.openConfigureModal();
};

TemplateBlock.prototype.saveButtonClicked = function (event)
{
    event.preventDefault();
    this.save();
    this.closeConfigureModal();
};

TemplateBlock.prototype.openConfigureModal = function ()
{
    $(this.configureModal).modal('show');
};

TemplateBlock.prototype.closeConfigureModal = function ()
{
    $(this.configureModal).modal('hide');
};

TemplateBlock.prototype.fill = function ()
{
    this.form.set('style', this.config.style);
};

TemplateBlock.prototype.save = function ()
{
    this.config.style = this.form.get('style');
};

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
    } else if (this.type === 'image') {
        this.object = new ImageObject(this.element, this.locale);
    } else if (this.type === 'tag') {
        this.object = new TagObject(this.uid, this.element, this.locale);
    } else if (this.type === 'collection') {
        this.object = new CollectionObject(this.element, this.locale);
    } else if (this.type === 'nomenclature') {
        this.object = new NomenclatureObject(this.element, this.locale);
    } else if (this.type === 'media') {
        this.object = new MediaObject(this.element, this.locale);
    } else if (this.type === 'carousel') {
        this.object = new CarouselObject(this.element, this.locale)
    } else if (this.type === 'tags') {
        this.object = new TagsObject(this.uid, this.element, this.locale)
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
    this.form.set('style', this.config.style);
    this.form.set('content', this.config.content[this.locale]);
    this.form.set('type', this.config.type);

    this.form.bind('content', this.config.content[this.locale]);
};

TextObject.prototype.save = function ()
{
    this.config.style                = this.form.get('style');
    this.config.content[this.locale] = this.form.get('content');
    this.config.type                 = this.form.get('type');

    this.form.bind('content', this.config.content[this.locale]);
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
    this.form.set('style', this.config.style);
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('placeholder', this.config.placeholder[this.locale]);
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('maxLength', this.config.maxLength);
    this.form.set('type', this.config.type);
    this.form.set('required', this.config.required);
    this.form.set('translatable', this.config.translatable);
    this.form.set('hideLabel', this.config.hideLabel);
    this.form.set('tag', this.config.tag);

    this.form.bind('label', this.config.label[this.locale]);
};

EditableTextObject.prototype.save = function ()
{
    this.config.style                    = this.form.get('style');
    this.config.label[this.locale]       = this.form.get('label');
    this.config.placeholder[this.locale] = this.form.get('placeholder');
    this.config.help[this.locale]        = this.form.get('help');
    this.config.maxLength                = this.form.get('maxLength');
    this.config.type                     = this.form.get('type');
    this.config.required                 = this.form.get('required');
    this.config.translatable             = this.form.get('translatable');
    this.config.hideLabel                = this.form.get('hideLabel');
    this.config.tag                      = this.form.get('tag');

    this.form.bind('label', this.config.label[this.locale]);
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
    this.form.set('style', this.config.style);
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('required', this.config.required);

    this.form.bind('link', this.config.label[this.locale]);
};

ButtonLinkObject.prototype.save = function ()
{
    this.config.style              = this.form.get('style');
    this.config.label[this.locale] = this.form.get('label');
    this.config.help[this.locale]  = this.form.get('help');
    this.config.required           = this.form.get('required');

    this.form.bind('link', this.config.label[this.locale]);
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
    this.form.set('style', this.config.style);
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('numberOfParticipantShown', this.config.numberOfParticipantShown);

    this.form.bind('participant', this.config.label[this.locale] + ' ' + this.config.numberOfParticipantShown);
};

ParticipantObject.prototype.save = function ()
{
    this.config.style                    = this.form.get('style');
    this.config.label[this.locale]       = this.form.get('label');
    this.config.numberOfParticipantShown = this.form.get('numberOfParticipantShown');

    this.form.bind('participant', this.config.label[this.locale] + ' ' + this.config.numberOfParticipantShown);
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
    this.form.set('style', this.config.style);
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('placeholder', this.config.placeholder[this.locale]);
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('required', this.config.required);
    this.form.set('products', this.config.products);

    this.form.bind('label', this.config.label[this.locale]);
};

ImageObject.prototype.save = function ()
{
    this.config.style                    = this.form.get('style');
    this.config.label[this.locale]       = this.form.get('label');
    this.config.placeholder[this.locale] = this.form.get('placeholder');
    this.config.help[this.locale]        = this.form.get('help');
    this.config.required                 = this.form.get('required');
    this.config.products                 = this.form.get('products');

    this.form.bind('label', this.config.label[this.locale]);
};


/**
 * CarouselObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function CarouselObject(element, locale)
{
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

CarouselObject.prototype.fill = function ()
{
    this.form.set('style', this.config.style);
    this.form.set('label', this.config.label[this.locale]);

    this.form.bind('label', this.config.label[this.locale]);
};

CarouselObject.prototype.save = function ()
{
    this.config.style              = this.form.get('style');
    this.config.label[this.locale] = this.form.get('label');
    this.config.required           = this.form.get('required');

    this.form.bind('label', this.config.label[this.locale]);
};

/**
 * TagObject
 *
 * @param uid
 * @param element
 * @param locale
 * @constructor
 */
function TagObject(uid, element, locale)
{
    this.uid     = uid;
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));

    [].forEach.call(this.element.querySelectorAll('[data-collection-bind]'), function (element) {
        element.setAttribute('data-collection', element.getAttribute('data-collection-bind'));
        $(element).collection();
    });
}

TagObject.prototype.fill = function ()
{
    this.form.set('style', this.config.style);
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('tag', this.config.tag);

    [].forEach.call(this.config.tags, function (tag, index) {
        this.form.set('tags[' + index + '][tag]', this.config.tags[index].tag);
    }.bind(this));

    this.form.bind('label', this.config.label[this.locale]);
};

TagObject.prototype.save = function ()
{
    this.config.style              = this.form.get('style');
    this.config.label[this.locale] = this.form.get('label');

    var indexes = [];

    [].forEach.call(this.element.querySelectorAll('.tags-item-' + this.uid), function (element) {
        var index = parseInt(element.getAttribute('data-index'));
        indexes.push(index);

        if (this.config.tags[index] === undefined) {
            this.config.tags[index] = {
                tag: null
            }
        }

        this.config.tags[index].tag = this.form.get('tags[' + index + '][tag]');
    }.bind(this));

    var tags = [];

    [].forEach.call(this.config.tags, function (tag, index) {
        if (-1 !== indexes.indexOf(index)) {
            tags.push(tag);
        }
    }.bind(this));

    this.config.tags = tags;

    this.form.bind('label', this.config.label[this.locale]);
};

/**
 * NomenclatureObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function NomenclatureObject(element, locale)
{
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

NomenclatureObject.prototype.fill = function ()
{
    this.form.set('style', this.config.style);
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('nomenclature', this.config.nomenclature);
    this.form.set('mode', this.config.mode);
    this.form.set('objective', this.config.objective);
    this.form.set('required', this.config.required);

    this.form.bind('label', this.config.label[this.locale]);
};

NomenclatureObject.prototype.save = function ()
{
    this.config.style              = this.form.get('style');
    this.config.label[this.locale] = this.form.get('label');
    this.config.help[this.locale]  = this.form.get('help');
    this.config.nomenclature       = this.form.get('nomenclature');
    this.config.mode               = this.form.get('mode');
    this.config.objective          = this.form.get('objective');
    this.config.required           = this.form.get('required');

    this.form.bind('label', this.config.label[this.locale]);
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
    this.form.set('style', this.config.style);
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('placeholder', this.config.placeholder[this.locale]);
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('required', this.config.required);
    this.form.set('default', this.config.default);
    this.form.set('translatable', this.config.translatable);

    this.form.bind('label', this.config.label[this.locale]);
};

CollectionObject.prototype.save = function ()
{
    this.config.style                    = this.form.get('style');
    this.config.label[this.locale]       = this.form.get('label');
    this.config.placeholder[this.locale] = this.form.get('placeholder');
    this.config.help[this.locale]        = this.form.get('help');
    this.config.required                 = this.form.get('required');
    this.config.default                  = this.form.get('default');
    this.config.translatable             = this.form.get('translatable');

    this.form.bind('label', this.config.label[this.locale]);
};

/**
 * TagsObject
 *
 * @param uid
 * @param element
 * @param locale
 * @constructor
 */
function TagsObject(uid, element, locale)
{
    this.uid     = uid;
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));

    [].forEach.call(this.element.querySelectorAll('[data-collection-bind]'), function (element) {
        element.setAttribute('data-collection', element.getAttribute('data-collection-bind'));
        $(element).collection();
    });
}

TagsObject.prototype.fill = function ()
{
    this.form.set('style', this.config.style);
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('collection', this.config.collection);
    this.form.set('placeholder', this.config.placeholder[this.locale]);
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('required', this.config.required);
    this.form.set('default', this.config.default);
    this.form.set('translatable', this.config.translatable);

    [].forEach.call(this.config.tags, function (tag, index) {
        this.form.set('tags[' + index + '][tag]', this.config.tags[index].tag);
        this.form.set('tags[' + index + '][label][' + this.locale + ']', this.config.tags[index].label[this.locale]);
    }.bind(this));

    this.form.bind('label', this.config.label[this.locale]);
};

TagsObject.prototype.save = function ()
{
    this.config.style                    = this.form.get('style');
    this.config.label[this.locale]       = this.form.get('label');
    this.config.collection               = this.form.get('collection');
    this.config.placeholder[this.locale] = this.form.get('placeholder');
    this.config.help[this.locale]        = this.form.get('help');
    this.config.required                 = this.form.get('required');
    this.config.default                  = this.form.get('default');
    this.config.translatable             = this.form.get('translatable');

    var indexes = [];

    [].forEach.call(this.element.querySelectorAll('.tags-item-' + this.uid), function (element) {
        var index = parseInt(element.getAttribute('data-index'));
        indexes.push(index);

        if (this.config.tags[index] === undefined) {
            this.config.tags[index] = {
                tag: null,
                label: {}
            }
        }

        this.config.tags[index].tag                = this.form.get('tags[' + index + '][tag]');
        this.config.tags[index].label[this.locale] = this.form.get('tags[' + index + '][label][' + this.locale + ']');

    }.bind(this));

    var tags = [];

    [].forEach.call(this.config.tags, function (tag, index) {
        if (-1 !== indexes.indexOf(index)) {
            tags.push(tag);
        }
    }.bind(this));

    this.config.tags = tags;

    this.form.bind('label', this.config.label[this.locale]);
};

/**
 * MediaObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function MediaObject(element, locale)
{
    this.element = element;
    this.locale  = locale;
    this.form    = new Form(element);
    this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

MediaObject.prototype.fill = function ()
{
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('titlePlaceholder', this.config.titlePlaceholder[this.locale]);
    this.form.set('linkPlaceholder', this.config.linkPlaceholder[this.locale]);
    this.form.set('translatable', this.config.translatable);
    this.form.set('max', this.config.max);
    this.form.set('default', this.config.default);
    this.form.set('products', this.config.products);

    this.form.bind('label', this.config.label[this.locale]);
};

MediaObject.prototype.save = function ()
{
    this.config.label[this.locale]            = this.form.get('label');
    this.config.titlePlaceholder[this.locale] = this.form.get('titlePlaceholder');
    this.config.linkPlaceholder[this.locale]  = this.form.get('linkPlaceholder');
    this.config.translatable                  = this.form.get('translatable');
    this.config.max                           = this.form.get('max');
    this.config.default                       = this.form.get('default');
    this.config.products                      = this.form.get('products');

    this.form.bind('label', this.config.label[this.locale]);
};

module.exports = TemplateBuilder;
