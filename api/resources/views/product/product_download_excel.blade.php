<table>
    <thead>
        <tr>
            <th width="40">Producto</th>
            <th  width="25">SKU</th>
            <th  width="25">Precio - Cliente Final</th>
            <th  width="25">Precio - Cliente Empresa</th>
            <th  width="25">Categoria</th>
            <th  width="25">Stock disponible</th>
            <th  width="25">¿Es regalo?</th>
            <th  width="25">¿Descuento?</th>
            <th  width="25">Tipo de Impuesto</th>
            <th  width="25">Importe Iva</th>
            <th  width="25">Disponibilidad</th>
            <th  width="20">Estado</th>
            <th  width="30">Dias de garantia</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($list_products as $list_product)
            <tr>
                <td>
                    {{$list_product->title}}
                </td>
                <td>
                    {{$list_product->sku}}
                </td>
                <td>
                    {{$list_product->price_general}}
                </td>
                <td>
                    {{$list_product->price_company}}
                </td>
                <td>
                    {{$list_product->product_categorie?->title}}
                </td>
                <td>
                    {{ $list_product->warehouses->sum('stock') }}
                </td>
                <td>
                    {{$list_product->is_gift ? 'SI' : 'NO'}}
                </td>
                <td>
                    {{$list_product->is_discount ? 'SI' : 'NO'}}
                    <br>
                    Descuento: {{$list_product->max_discount}} %
                </td>
                <td>
                    {{ $list_product->tax_selected ? 'Libre de Impuesto' : 'Sujeto a Impuesto' }}
                </td>
                <td>
                    {{$list_product->importe_iva}} %
                </td>
                <td>
                    {{ $list_product->disponibilidad ? 'No Vender sin Stock' : 'Vender sin Stock' }}
                </td>

                @if ($list_product->state)
                    <td style="background: #b6c8ff">
                        ACTIVO
                    </td>
                @else
                    <td style="background: #f7a6a6;">
                        INACTIVO
                    </td>
                @endif

                <td>
                    {{$list_product->warranty_day}} dias
                </td>
            </tr>
        @endforeach

    </tbody>
</table>