<?php

if (file_exists('../approot.inc.php'))
{
    // iTop 1.0.2
    include('../approot.inc.php');
}
else // iTop 1.0 & 1.0.1
{
    define('APPROOT', '../');
}
require_once(APPROOT.'application/application.inc.php');
require_once(APPROOT.'application/webpage.class.inc.php');
require_once(APPROOT.'application/csvpage.class.inc.php');
require_once(APPROOT.'application/clipage.class.inc.php');
require_once(APPROOT.'application/startup.inc.php');


$g_aUsersCache = null;      // Cache of all the iTop users to speed up searches
$g_aProfilesCache = null;   // Cache of all iTop profiles
$g_ContactosAsignados = null; // Cantidad de contactos asignados a un equipo, si es mas de dos no muestra el formato
$g_printFormat = null; //si es igual a 1 genera el formato

$title = "";
$ticket_id = "";
$usuario_entrega = "";
$fecha_solicitud = "";
$agente = "";
$usuario_entrega = "";
$fecha_aprobacion = "";
$aprobacion = "";
$autorizado = "";
$campos = "";
$campo = "";
$agente= "";
$mouse = "";
$mochila = "";

$ci = $_SERVER['QUERY_STRING'];

function get_string_between($string, $start, $end){
	$string = ' ' . $string;
        $ini = strpos($string, $start);
	if ($ini == 0) return '';
		$ini += strlen($start);
		$len = strpos($string, $end, $ini) - $ini;
		return substr($string, $ini, $len);
}

    global $g_aProfilesCache;

    $title = "Acta de entrega $ci";
?>
<html>
<head>
<title><?=$title?></title>
<!--
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

<link rel="stylesheet" href="../css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

<link rel="stylesheet" href="../css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
-->
<style>
@media print { .container { page-break-after: always; } }
.marco { outline: 1px solid green;}
</style>
</head>
<body>



<?php
    $sOQL = "SELECT lnkFunctionalCIToTicket WHERE functionalci_id_friendlyname =  '$ci'";
    $oSearch = DBObjectSearch::FromOQL($sOQL);
    $oTicketCi = new CMDBObjectSet($oSearch);

    // revisa si tiene algun ticket registrado
    if ( count($oTicketCi) == 0 ) { 
        ?>
            <div class="container">
                <div class="jumbotron">
                    <h1>No se encontró registro de ticket de asignación.</h1>
                </div>
            </div>
        <?php
        exit();
    }


    // En este caso es solo un ticket, y va a mostrar ese caso.
    while($oCi = $oTicketCi->Fetch())
    {
        // Se limpian las variables
        $autorizado = "No Aceptado";

        // Se obtienen las variables del query
        $ticket_id = $oCi->Get('ticket_id'); //Se obtiene el id del ticket
        $functionalci_id = $oCi->Get('functionalci_id');
        $class = $oCi->Get('functionalci_id_finalclass_recall'); // Obtiene el tipo de activo PC, celular, impresora
        // Se obtiene informacion del ticket        
        $sOQL = "SELECT UserRequest  WHERE id =  '$ticket_id'";
        $oSearch = DBObjectSearch::FromOQL($sOQL);
        $oTicket = new CMDBObjectSet($oSearch);

        while($oDetalleTicket = $oTicket->Fetch())
        {
            //Se valida que el ticket sea de asignacion
            $service_id = $oDetalleTicket->Get('service_id');
            if ( $service_id == 2 || $service_id == 26 ) //Es un ticket de asignacion
            {
                $caller_id = $oDetalleTicket->Get('caller_id');
                $fecha_solicitud = $oDetalleTicket->Get('start_date');
                $fecha_solicitud = substr($fecha_solicitud, 0, 10);
                $ref =  $oDetalleTicket->Get('ref');

                //Se obtiene la fecha de la aprobacion
                $sOQL = "SELECT UserRequestApprovalScheme WHERE obj_key = $ticket_id";
                $oSearch = DBObjectSearch::FromOQL($sOQL);
                $oAprobacion = new CMDBObjectSet($oSearch);

                while($oApproval = $oAprobacion->Fetch())
                {
                    $fecha_aprobacion = $oApproval->Get('ended');
                    $aprobacion = $oApproval->Get('status');
                    if ( $aprobacion == 'accepted' ) { $autorizado = "Aceptado"; }
                }

                // Se obtiene la informacion complementaria (Template)
                $sOQL = "SELECT TemplateExtraData WHERE obj_key = $ticket_id";
                $oSearchTemplate = DBObjectSearch::FromOQL($sOQL);
                $oTemplate = new CMDBObjectSet($oSearchTemplate);

                while($oFields = $oTemplate->Fetch())
                {
                    $campos = $oFields->Get('data');
                    $campo = explode(":",$campos);
                    $agente= get_string_between($campo[93], '"', '"');
                    $mouse = get_string_between($campo[73], '"', '"');
                    $mochila = get_string_between($campo[69], '"', '"');
                    if ( $mochila == "Si" ) { $mochila_chk = 'checked'; }
                    if ( $mouse == "Si" ) { $mouse_chk = 'checked'; }
                }

                // Se obtiene informacion del contacto
                $sOQL = "SELECT Contact WHERE id =  '$caller_id'";
                $oSearchContact = DBObjectSearch::FromOQL($sOQL);
                $oSetContact = new CMDBObjectSet($oSearchContact);

                while($oContact = $oSetContact->Fetch())
                {
                    $Contact_email = $oContact->Get('email');
                    $Contact_friendlyname = $oContact->Get('friendlyname');
                    $Contact_function = $oContact->Get('function');
                    $Contact_org_name = $oContact->Get('org_name');
                    $Contact_phone = $oContact->Get('phone');
                    $Contact_status = $oContact->Get('status');

                }

                // Se obtiene informacion de la persona
                $sOQL = "SELECT Person WHERE id =  '$caller_id'";
                $oSearchPerson = DBObjectSearch::FromOQL($sOQL);
                $oSetPerson = new CMDBObjectSet($oSearchPerson);

                while($oPerson = $oSetPerson->Fetch())
                {
                    $Person_employee_number = $oPerson->Get('employee_number');
                    $Person_location_name = $oPerson->Get('location_name');
                    $Person_manager_id_friendlyname = $oPerson->Get('manager_id_friendlyname');
                    $Person_mobile_phone = $oPerson->Get('mobile_phone');
                }

                // Se obtiene informacion del activo de la clase 
                $sOQL = "SELECT $class WHERE id = '$functionalci_id'";
                $oSearch = DBObjectSearch::FromOQL($sOQL);
                $oSet = new CMDBObjectSet($oSearch);
                if ( count($oSet) == 0 ) { exit; }
                while($oPC = $oSet->Fetch())
                {
                    switch($class)
                    {
                        case "PC":
                            // $PC_brand_name = $oPC->Get('brand_name');
                            // $PC_ci_id = $oPC->Getkey();
                            // $PC_location_name = $oPC->Get('location_name'); // required
                            // $PC_model_name = $oPC->Get('model_name');
                            // $PC_name = $oPC->Get('name');
                            // $PC_organization_name = $oPC->Get('organization_name'); 
                            // $PC_osfamily_name = $oPC->Get('osfamily_name');
                            // $PC_osversion_name = $oPC->Get('osversion_name');
                            // $PC_serialnumber = $oPC->Get('serialnumber');
                            // $PC_status = $oPC->Get('status'); // production
                            $CI_Tag = "PC";
                            $CI_brand_name = $oPC->Get('brand_name');
                            $CI_ci_id = $oPC->Getkey();
                            $CI_location_name = $oPC->Get('location_name'); // required
                            $CI_model_name = $oPC->Get('model_name');
                            $CI_name = $oPC->Get('name');
                            $CI_organization_name = $oPC->Get('organization_name'); 
                            $CI_osfamily_name = $oPC->Get('osfamily_name');
                            $CI_osversion_name = $oPC->Get('osversion_name');
                            $CI_serialnumber = $oPC->Get('serialnumber');
                            $CI_status = $oPC->Get('status'); // production
                            break;

                        case "MobilePhone":
                            $CI_Tag = "Celular";
                            $CI_name = $oPC->Get('name');
                            $CI_location_name = $oPC->Get('location_name'); // required
                            $CI_brand_name = $oPC->Get('brand_name');
                            $CI_model_name = $oPC->Get('model_name');
                            $CI_serialnumber = $oPC->Get('serialnumber');
                            $CI_imei = $oPC->Get('imei');
                            $CI_status = $oPC->Get('status'); // production
                            break;

                        default:
                            $CI_Tag = $class;
                            $CI_name = $oPC->Get('name');
                            $CI_location_name = $oPC->Get('location_name'); // required
                            $CI_brand_name = $oPC->Get('brand_name');
                            $CI_model_name = $oPC->Get('model_name');
                            $CI_serialnumber = $oPC->Get('serialnumber');
                            $CI_status = $oPC->Get('status'); // production
                            break;
                    }
                }

                // Obtine el Contacto que tiene el equipo asignado
                $sOQL = "SELECT lnkContactToFunctionalCI WHERE functionalci_id =  '$functionalci_id'";
                $oSearchContacts = DBObjectSearch::FromOQL($sOQL);
                $oSetContacts = new CMDBObjectSet($oSearchContacts);



                if ( count($oSetContacts) <> 1 ) 
                { 
                        ?>
                            <div class="container">
                                <div class="jumbotron">
                                    <h1>No esta asignado correctamente el usuario.</h1>
                                </div>
                            </div>
                        <?php
                        exit();
                }

                while($oContacts = $oSetContacts->Fetch())
                {
                    if ( $oContacts->Get('contact_id') <> $caller_id )
                    {
                            ?>
                                <div class="container">
                                    <div class="jumbotron">
                                        <h1>El usuario asignado no coincide con el usuario del acta.</h1>
                                    </div>
                                </div>
                            <?php
                            exit();                    
                    }
                }
                              
            }
            else  // No es un ticket de asignacion
            {
                ?>
                        <div class="container">
                            <div class="jumbotron">
                                <h1>No se encontró registro de ticket de asignación.</h1>
                            </div>
                        </div>
                <?php
                exit();                
            }
        }
        
    }


//Revisa faltantes
if ( $Contact_friendlyname == "" ) {$g_faltantes = $g_faltantes."$El contacto no tiene nombre";}
if ( $CI_name == "" ) {$g_faltantes = $g_faltantes."<ul>No esta el nombre de la CI.</ul>";}
// if ( $CI_serialnumber == "" ) {$g_faltantes = $g_faltantes."<ul>La CI no tiene numero de serie.</ul>";}
// if ( $Contact_email == "" ) {$g_faltantes = $g_faltantes."$El Contatco no tiene correo";}
// if ( $Contact_friendlyname == "" ) {$g_faltantes = $g_faltantes."$El contacto no tiene nombre";}
// if ( $Contact_function == "" ) {$g_faltantes = $g_faltantes."El contacto no tiene puesto.";}
// if ( $Contact_org_name == "" ) {$g_faltantes = $g_faltantes."El contacto no tiene organizacion.";}
// if ( $Contact_phone == "" ) {$g_faltantes = $g_faltantes."El contacto no tiene telefono.";}
// if ( $Contact_status <> "active" ) {$g_faltantes = $g_faltantes."El Contacto no esta activo.";}
// if ( $g_ContactosAsignados <> 1 ) {$g_faltantes = $g_faltantes."<ul>Mas de un contacto Asignado</ul>";}
// if ( $CI_brand_name == "" ) {$g_faltantes = $g_faltantes."<ul>La CI no tiene marca seleccionada.</ul>";}
if ( $CI_location_name == "" ){$g_faltantes = $g_faltantes."<ul>La CI no tiene centro de costos asignado.</ul>";}
// if ( $CI_model_name == "" ) {$g_faltantes = $g_faltantes."<ul>La CI no tiene modelo seleccionado.</ul>";}
// if ( $CI_name == "" ) {$g_faltantes = $g_faltantes."<ul>No esta el nombre de la CI.</ul>";}
// if ( $CI_organization_name == "" ) {$g_faltantes = $g_faltantes."<ul>La CI no tiene organizacion seleccionada.</ul>";}
// if ( $CI_osfamily_name == "" ) {$g_faltantes = $g_faltantes."<ul>La CI no tiene Sistema Operativo.</ul>";}
// if ( $CI_osversion_name == "" ) {$g_faltantes = $g_faltantes."<ul>La CI no tiene version de sistema operativo.</ul>";}
// if ( $CI_serialnumber == "" ) {$g_faltantes = $g_faltantes."<ul>La CI no tiene numero de serie.</ul>";}
if ( $CI_status  <> "production" ) {$g_faltantes = $g_faltantes."<ul>El estatus de la CI es '$CI_status'.</ul>";}
// if ( $Person_employee_number == "" ) {$g_faltantes = $g_faltantes."El contacto no tiene numero de empleado";}
// if ( $Person_location_name == "" ) {$g_faltantes = $g_faltantes."El contacto no tiene centro de costos.";}
// if ( $Person_manager_id_friendlyname == "" ) {$g_faltantes = $g_faltantes."El contacto no tiene jefe";}
// if ( $Person_mobile_phone == "" ) {$g_faltantes = $g_faltantes."El contacto no tiene celular.";}
// if ( $User_first_name == "" ) {$g_faltantes = $g_faltantes."No hay sesion iniciada.";}
// if ( $User_last_name == "" ) {$g_faltantes = $g_faltantes."No hay sesion iniciada.";}

$g_faltantes = '';

if ( $g_faltantes <> "" ){
?>
        <div class="container">
            <div class="jumbotron">
                <h1><?=$g_faltantes;?></h1>
            </div>
        </div>
<?php
exit();                

}
?>

    <div class="container">
        <div class="jumbotron">
            <h1>COSMOCEL SA</h1>
            <p>Acta de entrega de <?=$CI_Tag?></p>
            <p align="right">Solicitud de entrega: <?=$ref?></p>
        </div>
        <p class="justified small">
            El usuario responsable del equipo reconoce que el equipo y el software incluido con este son propiedad de COSMOCEL, S. A., por lo cual mediante la firma de este documento se compromete a no realizar copias o hacer mal uso del Hardware y del Software incluido con este equipo.  
        </p>
        <div class="marco">
        <table class="table  border-spacing: 5em;" width="100%" >
            <tr><th colspan="2">Empleado Responsable</th></tr>
            <tr><td class="font-weight-bold" width="25%">Nombre</td><td><?=$Contact_friendlyname?></td></tr>
            <tr><td class="font-weight-bold">Número de empleado</td><td><?=$Person_employee_number?></td></tr>
            <tr><td class="font-weight-bold">Puesto</td><td><?=$Contact_function?></td></tr>
            <tr><td class="font-weight-bold">Centro de Costos</td><td><?=$CI_location_name?></td></tr>
            <tr><th colspan="2">&nbsp;</th></tr>
            <tr><th colspan="2">Equipos Asignado</th></tr>
            <tr><td class="font-weight-bold">Nombre del equipo</td><td><?=$CI_name?></td></tr>
            <tr><td class="font-weight-bold">Marca</td><td><?=$CI_brand_name?></td></tr>
            <tr><td class="font-weight-bold">Modelo</td><td><?=$CI_model_name?></td></tr>
            <tr><td class="font-weight-bold">Número de serie</td><td><?=$CI_serialnumber?></td></tr>
            <?php
                if ($class == "Celular"){
                    ?>
                    <tr><td class="font-weight-bold">IMEI</td><td><?=$CI_imei?></td></tr>
                    <?php
                }

            ?>
        <tr><th colspan="2">&nbsp;</th></tr>

            <tr><th colspan="2">Aceptación de políticas</th></tr>
            <tr><td colspan="2" ><small><input type="checkbox" class="chkbox">
            Leí y acepto la Política seguridad en equipos e información y de Uso de Computadoras, Red, Correo Electrónico e Internet. </small>
        </td></tr>
            <tr><th colspan="2">&nbsp;</th></tr>
            <tr><th colspan="2">Entrega de equipo</th></tr>
            <tr><th colspan="2">&nbsp;</th></tr>
            <tr><th colspan="2">
                <table class="table-condensed" width="100%">
            <tr>
                <td align="center">Fecha de entrega <br><br> <?=$fecha_solicitud;?></td>
                <td align="center">Entregado por <br><br><?=$agente;?></td>
                <td align="center">Recibido por <br><br><?=$Contact_friendlyname;?></td>
            </tr>
                </table>
            </th></tr>
            <tr><th colspan="2">&nbsp;</th></tr>
            <tr><td colspan="2"><font size="-2">
                * Nota: Por favor de verificar que recibió el equipo con los componentes listados anteriormente, en caso de que falte alguno notifíquelo inmediatamente al departamento de sistemas de COSMOCEL, S. A., si recibió todos los componentes por favor de anotar la fecha de recepción (Punto 4), firme el formato en el renglón correspondiente y regréselo al área de sistemas de COSMOCEL, S. A. </font>
            </td></tr>            
</table></div>
<br><br><br>
</div>

<div class="container" style="break-after: always">
    <p class="text-justify"><font size="-2">
<strong>Política de Uso de Computadoras, Red, Correo Electrónico e Internet.</strong> 1.- Objetivo. Establecer las reglas de utilización de Computadoras, Red, Correo Electrónico e Internet propiedad de Cosmocel S.A., con la finalidad de optimizar y garantizar el buen funcionamiento de los mismos, así como proteger la información del negocio. 2. Alcance. Aplica a todos los empleados, contratistas independientes, trabajadores temporales y cualquier otro usuario de los recursos de tecnología de información de Cosmocel, S.A. 3. Documentos de Referencia. Ninguno. 4. Definiciones. TI.- Tecnología de Información. Recursos de TI.- Computadoras, Red, Correo Electrónico, Internet y cualquier otro medio o sistema de información y comunicación electrónica propiedad de Cosmocel S.A. Usuarios de TI.- Empleados, Contratistas independientes, Trabajadores Temporales y cualquier otra persona que preste sus servicios a la Compañía, que requiera utilizar los recursos de TI. 5. Política. 5.1 Premisas básicas. Los recursos de TI son propiedad de Cosmocel y representan una herramienta de trabajo para que los usuarios puedan desempeñar sus funciones adecuadamente, por lo que su utilización deberá limitarse al servicio y en beneficio de la Compañía. Su utilización no deberá representar ningún daño o costo innecesarios para Cosmocel. 5.2 Responsabilidades Individuales Es responsabilidad de cada uno de los usuarios conocer y aplicar de manera estricta los lineamientos establecidos en la presente política LA FALTA DE SU CUMPLIMIENTO PROVOCARÁ MEDIDAS DISCIPLINARIAS Y / O ADMINISTRATIVAS, INCLUYENDO CUANDO SEA NECESARIO, EL DESPIDO DEL USUARIO Y EN SU CASO ACCIÓN LEGAL. 5.3 Uso Privado de los Recursos de IT. Cosmocel proporciona a los usuarios el acceso a los recursos de TI para garantizar una mayor eficiencia en el desempeño de sus funciones, por lo que queda estrictamente prohibido el mal uso de los mismos; sin embargo, se acepta el uso razonable y mínimo del correo electrónico e Internet por motivos personales, siempre y cuando: a) Su uso se lleve a cabo durante los descansos y / o fuera del horario de trabajo, excepto en caso de una emergencia. b) Su utilización no interfiera con los compromisos directos o indirectos con Cosmocel. c) Su uso no ocasione costos adicionales para Cosmocel. d) Su utilización siga de manera estricta los lineamientos establecidos en esta política. 5.4 Protección e integridad del sistema de Cosmocel. Para evitar la diseminación de virus y el acceso a la información por personal no autorizado, los recursos de TI propiedad de Cosmocel, están protegidos mediante sistemas, software antivirus y servidores de seguridad (firewalls); no obstante, los usuarios deberán seguir todas las medidas de seguridad e indicaciones que el área de TI establezca, tales como: a) Todas las conexiones y otros ajustes de las computadoras o equipo de la red de la compañía deben contar con la aprobación previa del Área de TI, sin excepciones. b) El Usuario es responsable de la salvaguarda física del equipo asignado, así como de la seguridad de la información que contenga el mismo. c) El Usuario no debe divulgar ni compartir las contraseñas o códigos de acceso, a otra persona. d) El Usuario debe tomar todas las medidas razonables para proteger los recursos de tecnología de información de Cosmocel contra virus, acceso no autorizado y otros ataques contra la integridad y seguridad del sistema. 5.5 Uso de Internet. Queda estrictamente prohibido ver, guardar, descargar o hacer circular material sexualmente explícito o pornográfico o insultante incluyendo entre otros: cualquier material que pudiera ser percibido como ofensivo por motivos raciales, de orientación sexual, país de origen, género, discapacidad, creencias religiosas o políticas o exhibir cualquier material de ese tipo en cualquier computadora de Cosmocel (por ejemplo, como protector de pantalla). De igual manera, se prohíbe cualquier comentario a través de correo electrónico con respecto a los asuntos descritos anteriormente. Se prohíbe el acceso o la participación en Internet en grupos de discusión o sitios de charla (chat), a menos que sean únicamente por motivos estrictamente relacionados con la función de los usuarios, en cuyo caso el área de IT deberá autorizar su uso. El acceso a Internet desde las computadoras de Cosmocel solamente está permitido mediante los métodos autorizados y controlados por Cosmocel. 5.6 Uso del Correo Electrónico y los Sistemas de Comunicación Electrónica.  Los mensajes que se envían a través de Internet y del sistema de correo electrónico pueden provocar una acción legal contra Cosmocel. El mal o descuidado uso de estas herramientas puede provocar reclamaciones por difamación, incumplimiento de confidencialidad o de contrato. Los mensajes de correo electrónico y de Internet deben considerarse como cualquier otra forma de correspondencia formal; por lo tanto, el contenido y el lenguaje utilizado deben ser consistentes con el Código de Conducta de Cosmocel. Los mensajes electrónicos enviados por cualquier persona que utilice el sistema de correo o el acceso a Internet de Cosmocel pueden ser archivados fácilmente por terceros y ser sujetos a búsquedas durante un largo período de tiempo. Los mensajes enviados por correo electrónico pueden ser divulgados en una acción legal emprendida contra Cosmocel, en donde sea pertinente a los asuntos del litigio. Todas las actividades llevadas a cabo usando una cuenta de Internet de Cosmocel (por ejemplo, Inicial del Nombre+Apellido@cosmocel.com) reflejan directamente la imagen y reputación de Cosmocel. Se prohíbe que los usuarios realicen actividades en tales cuentas que pudieran comprometer o dañar la imagen o reputación de Cosmocel. Las direcciones de correo electrónico no deben utilizarse en grupos de discusión privada, como receptores de publicidad privada ni de ninguna otra manera que pudiera dañar a Cosmocel. La información de la compañía no debe residir en servidores fuera del control de Cosmocel, por ejemplo, queda estrictamente prohibido remitir correo electrónico empresarial o documentos hacia servicios públicos como Hotmail.com, Gmail.com, Dropbox, etc.  Los mensajes de correo electrónico y anexos clasificados como "Confidenciales" o "Estrictamente confidenciales" deben protegerse adecuadamente utilizando los métodos de seguridad establecidos por el área de TI. Las firmas digitales deben usarse en aquellos correos electrónicos en los que se debe asegurar la integridad del contenido o la responsabilidad legal del remitente. Queda prohibido distribuir cadenas de cartas. 5.7 Propiedad intelectual y protección de la información. Los derechos de propiedad intelectual, la información confidencial y los datos de Cosmocel, al igual que la propiedad intelectual, la información y los datos de sus clientes y socios son recursos corporativos de gran valor. Por lo tanto, deben protegerse contra la alteración deliberada, no intencional o no autorizada, la duplicación, destrucción o divulgación inapropiada o la diseminación y deben usarse según las leyes y reglamentos en los países o estados en los cuales opera Cosmocel. La legislación de propiedad intelectual, por ejemplo, los derechos de autor y patentes, prohíbe la duplicación y alteración del material sin la autorización pertinente. El Usuario no debe distribuir, descargar o cargar software pirata, libre o cualquier material que incluya, entre otros, música, sonido, películas, grabaciones audiovisuales, textos, bases de datos, imágenes, fotografías y logotipos sin obtener antes los permisos necesarios de los propietarios de tales obras. El no cumplimiento de este punto puede generar para Cosmocel una responsabilidad ya que existe el compromiso por parte de la Compañía de cumplir con todas las disposiciones al respecto lo que podría ocasionar desembolsos innecesarios en caso de que sean detectados en los equipos propiedad de la empresa. 5.8 Registro de información personal. El registro de información personal en una base de datos podría estar sujeto a una legislación específica en diferentes sistemas legales. Siempre deberá tenerse en cuenta tal legislación. 5.9 Vigilancia del uso de los recursos de TI. El uso de los recursos de TI podrá ser vigilado sin aviso ni permiso previo en situaciones en que a criterio de la administración sea legítimo por motivos empresariales. Éstas incluyen, pero no se limitan a: vigilancia en el curso regular de la administración del sistema y la red; vigilancia para la investigación de conductas sospechosas o fallas de desempeño, incluyendo la investigación de sospecha de comportamiento desleal, conducta contraria a las políticas de la compañía (incluyendo la sospecha de incumplimiento de esta política) o fraude y la vigilancia para la protección de la información confidencial contra la posible diseminación sin permiso y cualquier otra situación en la cual la administración crea que existe un motivo suficiente para vigilar el uso de Internet por parte de los usuarios. La administración también podrá verificar el contenido de los archivos y cualquier mensaje guardado si lo considera pertinente por motivos puramente empresariales, que incluyen pero no se limitan a, verificar el correo electrónico relativo al trabajo mientras el usuario no está disponible, para garantizar que se abordan los asuntos urgentes; la investigación de una sospecha de conducta o desempeño insuficiente incluyendo el probable incumplimiento de esta política; para detectar un fraude o para proteger la información confidencial cuando se sospecha una diseminación inapropiada y en cualquier otra circunstancia en la cual la administración crea que existen los motivos empresariales legítimos para examinar los mensajes guardados. Podría requerirse que Cosmocel divulgue la información guardada de manera electrónica a terceros de acuerdo a procedimientos legales o si así lo requieren las autoridades. La vigilancia y la verificación de los resultados de la misma, solamente serán realizados por un número definido y determinado de personas. 
<strong>Política de Seguridad en Equipos e Información</strong> 1.- Objetivo. Establecer las bases para reglamentar e informar a cada uno de los empleados de COSMOCEL la política de Seguridad en Equipos e Información, resaltando las consecuencias de no contar ni seguir con las indicaciones del presente documento. 2. Alcance. Cosmocel S.A. 3. Documentos de Referencia. Ninguno. 4. Definiciones. Seguridad de los Equipos de Cómputo, Servidores, PC, Conmutador, así como protección a los Sistemas de Información. 5. Política. 5.1 Responsabilidades Individuales Es responsabilidad de cada uno de los usuarios conocer y aplicar de manera estricta los lineamientos establecidos en la presente política LA FALTA DE SU CUMPLIMIENTO PROVOCARÁ MEDIDAS DISCIPLINARIAS Y / O ADMINISTRATIVAS, INCLUYENDO CUANDO SEA NECESARIO, EL DESPIDO DEL USUARIO Y EN SU CASO ACCIÓN LEGAL. 5.2 Equipos de Cómputo y Comunicación. Las áreas de Informática y Sistemas de Cosmocel (Cuartos de equipos de cómputo, Cuartos de Comunicaciones, Conmutador, etc.) deberán tener acceso restringido a personas no autorizadas. Todos los funcionarios autorizados por la autoridad informática respectiva deberán portar identificación en lugar visible, permitiendo con esto una mejor identificación y control de las personas que ingresan a las áreas de cómputo restringidas y cuando lo hagan, éstas deberán de estar acompañadas de un miembro de la Gerencia de Sistemas. Solo personal técnico autorizado por La Gerencia de Sistemas puede revisar, configurar y dar soporte a los bienes informáticos de la Empresa. 5.3 Sistemas de Información. Los usuarios tienen la obligación de cambiar cada tres meses su clave de acceso, de acuerdo a los lineamientos establecidos por la Gerencia de Sistemas. Todas las aplicaciones que se utilicen deben tener clave de acceso y establecer perfiles de usuario para acceder a la información. Las palabras claves no deben aparecer en la pantalla al ser ingresadas, tampoco deben imprimirse o mantenerse en la máquina, y mucho menos en un medio que se encuentre en lugar visible. 5.4 Respaldos Es responsabilidad de la Gerencia de Sistemas llevar a cabo respaldos diarios en cintas a los servidores, mismas que será resguardadas en medios seguros. Mínimo una vez al año, personal de sistemas recorrerá las máquinas de cada uno de los usuarios con el fin de respaldar la información en discos duros portátiles, mismos que serán resguardados en la caja de seguridad de la empresa destinada para esos fines. 5.5 Alta de Personal. De acuerdo a los requerimientos de las tareas específicas a desempeñar en Cosmocel, es responsabilidad de la Gerencia de Sistemas de dotar del equipo y servicios necesarios al empleado para el cumplimiento de sus tareas. 5.6 Retiro de Personal. Cuando un usuario termine la relación laboral con Cosmocel o bien sea removido de su puesto de trabajo de manera provisional o permanente, deberá hacer entrega formal del equipo a su cargo, las claves de acceso e instruir a su reemplazo en la utilización del software que administra
</font></p>






</body>
</html>
