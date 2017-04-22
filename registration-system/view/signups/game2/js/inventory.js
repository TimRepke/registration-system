class Inventory {

    constructor() {
        this.possibleItems = {
            cheese: {
                name: 'Käse',
                merge: {
                    add: 'key',
                    produce: 'salt'
                }
            },
            key: {
                name: 'Schlüssel',
                merge: {
                    add: 'cheese',
                    produce: 'salt'
                }
            },
            salt: {
                name: 'Salzstreuer',
                max: 2
            }
        };

        this.foundItems = [];

        this.inventoryBucket = document.getElementById('inventory-bucket');
    }

    hasItem(name) {
        return this.foundItems.indexOf(name) >= 0;
    }

    hasItemMaxCount(name) {
        if (!this.hasItem(name)) return false;
        if ((typeof this.possibleItems[name] !== 'object') || !('max' in this.possibleItems[name]))
            return this.hasItem(name);
        var cnt = 0;
        this.foundItems.forEach((val, i) => {
            if (val == name) cnt++;
        });
        return cnt >= this.possibleItems[name]['max'];
    }

    isItemPossible(name) {
        return name in this.possibleItems;
    }

    addItem(name) {
        if (this.isItemPossible(name) && !this.hasItemMaxCount(name)) {
            var itemImg = new Image();
            itemImg.src = Environment.fapi.resolvePath('graphics/inventory/' + name + '.png');
            itemImg.id = 'inventory-item-img-' + name;
            itemImg.setAttribute('draggable', true);
            itemImg.addEventListener('dragstart', Inventory.dragDragStartHandler, false);

            var tooltip = document.createElement('span');
            tooltip.appendChild(document.createTextNode(this.possibleItems[name].name));
            tooltip.className = 'inventory-elem-tooltip';

            // wrap image in span
            var itemSpan = document.createElement('span');
            itemSpan.className = 'inventory-elem';
            itemSpan.id = 'inventory-item-' + name;
            itemSpan.addEventListener('drop', Inventory.dragDropHandler, false);
            itemSpan.addEventListener('dragleave', Inventory.dragDragLeaveHandler, false);
            itemSpan.addEventListener('dragover', Inventory.dragDragOverHandler, false);

            itemSpan.appendChild(tooltip);
            itemSpan.appendChild(itemImg);

            // add to bucket
            this.inventoryBucket.appendChild(itemSpan);
            this.foundItems.push(name);
        }
    }

    mergeItems(nameA, nameB) {
        if ((typeof this.possibleItems[nameA] === 'object') &&
            ('merge' in this.possibleItems[nameA]) &&
            (this.possibleItems[nameA]['merge']['add'] == nameB)) {
            this.addItem(this.possibleItems[nameA]['merge']['produce']);
        }
    }

    static dragDragStartHandler(ev) {
        ev.dataTransfer.setData('text', ev.target.id);
    }

    static dragDropHandler(ev) {
        ev.preventDefault();
        ev.currentTarget.style.background = 'initial';
        inv.mergeItems(ev.dataTransfer.getData('text').replace('inventory-item-img-',''),
            ev.currentTarget.id.replace('inventory-item-',''));
    }

    static dragDragOverHandler(ev) {
        ev.preventDefault();
        ev.currentTarget.style.background = 'red';
    }

    static dragDragLeaveHandler(ev) {
        ev.preventDefault();
        ev.currentTarget.style.background = 'initial';
    }
}