#!/usr/bin/env python3
import os
import sys
import argparse

# Check dependencies
try:
    import pandas as pd
    import requests
except ImportError:
    print("Error: Se requieren las librerías 'pandas', 'openpyxl' y 'requests'.")
    print("Puedes instalarlas ejecutando:")
    print("  pip install pandas openpyxl requests")
    sys.exit(1)

def main():
    parser = argparse.ArgumentParser(description="Script para crear usuarios en bloque a partir de un archivo Excel.")
    parser.add_argument("excel_path", help="Ruta al archivo Excel (.xlsx o .xls)")
    parser.add_argument("--url", default="http://localhost/negocio/configuracion/usuarios/procesarIngresarUsuario.php",
                        help="URL completa del endpoint procesarIngresarUsuario.php")
    
    args = parser.parse_args()
    
    excel_path = args.excel_path
    endpoint_url = args.url

    if not os.path.exists(excel_path):
        print(f"Error: El archivo Excel no existe en la ruta: {excel_path}")
        sys.exit(1)

    print(f"Leyendo archivo Excel: {excel_path}...")
    try:
        # Read the excel file. We fill NaN values with empty strings or default values
        df = pd.read_excel(excel_path)
    except Exception as e:
        print(f"Error al leer el archivo Excel: {e}")
        sys.exit(1)

    # Check for required columns
    required_columns = ['nombre', 'apellidoP', 'nombreUsuario', 'contrasena', 'idArea', 'idPerfil', 'idContrato']
    missing_columns = [col for col in required_columns if col not in df.columns]
    if missing_columns:
        print(f"Error: Al archivo Excel le faltan las siguientes columnas requeridas: {missing_columns}")
        print("Las columnas esperadas son:")
        print("- nombre (Requerido)")
        print("- apellidoP (Requerido)")
        print("- apellidoM (Opcional)")
        print("- correo (Opcional)")
        print("- nombreUsuario (Requerido)")
        print("- contrasena (Requerido)")
        print("- idArea (Requerido - numérico)")
        print("- idPerfil (Requerido - numérico)")
        print("- idContrato (Requerido - numérico)")
        print("- idUsuarioSubContratos (Opcional - lista de IDs separados por coma, ej: '1,2')")
        sys.exit(1)

    print(f"Se encontraron {len(df)} registros a procesar. Iniciando peticiones al endpoint...")
    print("-" * 80)

    success_count = 0
    failure_count = 0

    for index, row in df.iterrows():
        # Handle nan values
        nombre = str(row['nombre']).strip() if pd.notna(row['nombre']) else ""
        apellido_p = str(row['apellidoP']).strip() if pd.notna(row['apellidoP']) else ""
        apellido_m = str(row['apellidoM']).strip() if pd.notna(row['apellidoM']) else ""
        correo = str(row['correo']).strip() if pd.notna(row['correo']) else ""
        username = str(row['nombreUsuario']).strip() if pd.notna(row['nombreUsuario']) else ""
        contrasena = str(row['contrasena']).strip() if pd.notna(row['contrasena']) else ""
        
        # Numeric values should be integers
        id_area = int(row['idArea']) if pd.notna(row['idArea']) else ""
        id_perfil = int(row['idPerfil']) if pd.notna(row['idPerfil']) else ""
        id_contrato = int(row['idContrato']) if pd.notna(row['idContrato']) else ""

        # Validate basic required fields
        if not nombre or not apellido_p or not username or not contrasena or id_area == "" or id_perfil == "" or id_contrato == "":
            print(f"Fila {index + 2}: Omitida debido a campos requeridos vacíos (usuario: '{username}').")
            failure_count += 1
            continue

        # Form data as list of tuples to support duplicate keys (for the subcontracts array)
        data = [
            ('nombre', nombre),
            ('apellidoP', apellido_p),
            ('apellidoM', apellido_m),
            ('correo', correo),
            ('nombreUsuario', username),
            ('contrasena', contrasena),
            ('idArea', str(id_area)),
            ('idPerfil', str(id_perfil)),
            ('idContrato', str(id_contrato)),
        ]

        # Parse subcontracts if present (expected comma-separated values like "1,2,3")
        sub_contracts_str = str(row['idUsuarioSubContratos']).strip() if 'idUsuarioSubContratos' in df.columns and pd.notna(row['idUsuarioSubContratos']) else ""
        if sub_contracts_str:
            # Split and clean IDs
            sub_contracts = [s.strip() for s in sub_contracts_str.split(',') if s.strip().isdigit()]
            for sub_id in sub_contracts:
                data.append(('idUsuarioSubContratos[]', sub_id))

        # Send POST request
        try:
            response = requests.post(endpoint_url, data=data, timeout=10)
            response_text = response.text.strip()
            
            if response_text == "1":
                print(f"Fila {index + 2}: Usuario '{username}' creado exitosamente.")
                success_count += 1
            elif response_text == "-1":
                print(f"Fila {index + 2}: Error al crear usuario '{username}' (El script del servidor retornó -1).")
                failure_count += 1
            else:
                print(f"Fila {index + 2}: Respuesta inesperada del servidor para usuario '{username}': {response_text}")
                failure_count += 1
        except Exception as e:
            print(f"Fila {index + 2}: Excepción al conectar con el servidor para '{username}': {e}")
            failure_count += 1

    print("-" * 80)
    print("Proceso finalizado.")
    print(f"Creados exitosamente: {success_count}")
    print(f"Errores/Omitidos: {failure_count}")

if __name__ == "__main__":
    main()
