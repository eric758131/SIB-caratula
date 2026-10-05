import { HelpInfo } from '../../core/models/procedure.models';
import { HelpContent, StepId, UPLOAD_EXTENSIONS, UPLOAD_MAX_MB } from './wizard.models';

/** Guía general de cada paso: se muestra cuando no hay nada seleccionado */
export const STEP_HELP: Record<StepId, HelpContent> = {
  1: {
    icon: '🗂️',
    eyebrow: 'Paso 1 de 5',
    title: 'Define el tipo de proyecto',
    tips: [
      'Selecciona la categoría principal (por ejemplo, Instalaciones Eléctricas).',
      'Luego la categoría secundaria que corresponde a tu trabajo.',
      'Por último el tipo de proyecto específico. Si elegiste INFORMES, en lugar de un tipo escribes el tema del informe.',
      'Al elegir el tipo de proyecto verás los documentos que debes descargar y entregar.',
    ],
    warning: 'Pasa el mouse o haz clic sobre cualquier opción para ver aquí su explicación, ejemplos e imágenes.',
  },
  2: {
    icon: '📝',
    eyebrow: 'Paso 2 de 5',
    title: 'Completa los datos del proyecto',
    tips: [
      'Escribe el título del proyecto: así aparecerá en la carátula.',
      'Completa los parámetros (los que tienen * son obligatorios). En los números puedes usar coma o punto.',
      'Si el proyecto pide ubicación, márcala en el mapa: calle, zona y municipio se copian solos.',
      'Agrega la norma de diseño, verificación o consulta que corresponde a tu proyecto, informe o peritaje.',
      'Registra a los propietarios o solicitantes (personas o empresas): su nombre aparecerá en la carátula tal como lo escribas.',
      'Agrega al ingeniero o ingenieros proyectistas con su RNI completo y marca al principal con ★.',
    ],
    warning: 'Solo aparecen ingenieros habilitados. Si un ingeniero está suspendido no podrá firmar la carátula.',
  },
  3: {
    icon: '📎',
    eyebrow: 'Paso 3 de 5',
    title: 'Sube los documentos',
    tips: [
      'Tu trámite ya se guardó como borrador: podrás seguir editándolo hasta enviarlo.',
      'Cada documento requerido tiene su propia zona: arrastra tus archivos o haz clic para elegirlos.',
      'Puedes subir varios archivos por documento (por ejemplo, varios planos).',
      `Formatos aceptados: ${UPLOAD_EXTENSIONS.join(', ').toUpperCase()}. Máximo ${UPLOAD_MAX_MB} MB por archivo.`,
      'Si el documento tiene formato oficial, usa "Descargar formato", complétalo y súbelo.',
    ],
  },
  4: {
    icon: '👀',
    eyebrow: 'Paso 4 de 5',
    title: 'Revisa tu carátula y envía',
    tips: [
      'Verifica que el título, los parámetros y los nombres estén bien escritos.',
      'El espacio en blanco sobre cada nombre es para el sello y la firma del ingeniero.',
      'Si algo está mal, vuelve a "Corregir datos": la carátula se actualiza sola.',
    ],
    warning: 'Una vez enviado, el trámite ya no se puede modificar. Revisa todo antes de enviar.',
  },
  5: {
    icon: '✅',
    eyebrow: 'Paso 5 de 5',
    title: '¡Trámite enviado!',
    tips: [
      'Imprime la carátula en hoja tamaño carta, séllala y fírmala.',
      'Apersónate a las oficinas de la SIB La Paz para concluir el registro de tu visado (consulta el flujograma de procedimientos).',
    ],
  },
};

/** Convierte la ayuda que viene de la API (categorías, parámetros, títulos) al formato del panel */
export function helpFromInfo(info: HelpInfo, eyebrow: string, icon = '📘', extra: Partial<HelpContent> = {}): HelpContent {
  const hasContent = !!(
    info.description || info.example || info.important_notes || info.image_1 || info.image_2 || info.image_3 ||
    info.definition
  );

  return {
    icon,
    eyebrow,
    title: info.name,
    description: hasContent ? info.description : 'Esta opción todavía no tiene una descripción cargada.',
    example: info.example,
    importantNotes: info.important_notes,
    image1: info.image_1 ?? null,
    image2: info.image_2 ?? null,
    image3: info.image_3 ?? null,
    definition: info.definition ?? null,
    isItem: true,
    ...extra,
  };
}
