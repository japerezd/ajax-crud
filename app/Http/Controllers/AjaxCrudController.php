<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AjaxCrud;
use Validator;

class AjaxCrudController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()->ajax()){
            // metodo latest devolvera ultimos datos de la tabla ajax-crud
            return datatables()->of(AjaxCrud::latest()->get())
            ->addColumn('action', function($data){
                //edit and delete button
                $button = '<button type="button" name="edit" id="'.$data->id.'" class="edit btn btn-primary btn-sm">Edit</button>';
                $button.= '&nbsp;&nbsp;';
                $button.= '<button type="button" name="delete" id="'.$data->id.'" class="delete btn btn-danger btn-sm">Delete</button>';
                return $button; //desplegará el boton delete y edit debajo de la columna action
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('ajax_index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //validando datos de formulario
        $rules = array(
            'first_name' => 'required',
            'last_name'  => 'required',
            'image'      => 'required|image|max:2048'
        );
        // creara una instancia de validacion si hay error, lo almacena en $error
        $error = Validator::make($request->all(), $rules);

        if($error->fails()){
            return response()->json(['errors'=>$error->errors()->all()]);
        }

        // tenemos almacenada la imagen seleccionada
        $image = $request->file('image');
        // nuevo nombre con extension original de la imagen
        $new_name = rand().'.'.$image->getClientOriginalExtension();
        // esta imagen se movera a la carpeta public/images
        $image->move(public_path('images'), $new_name);
        
        $form_data = array(
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'image'      => $new_name
        );
        // insertara datos en la tabla ajax_cruds
        AjaxCrud::create($form_data);

        //para enviar respuesta a peticiones ajax
        return response()->json(['success' => "Datos añadidos exitosamente."]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(request()->ajax()){
            // nos devolvera el modelo relacionado basado en su PK
            $data = AjaxCrud::findOrFail($id);
            return response()->json(['data' =>$data]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $image_name = $request->hidden_image;
        $image = $request->file('image');
        if($image != ''){
            $rules = array(
                'first_name' => 'required',
                'last_name'  => 'required',
                'image'      => 'image|max:2048'
            );
            $error= Validator::make($request->all(),$rules);
            //sera true si falla los datos del form en cualquier regla de validacion
            if($error->fails()){
                return response()->json(['error'=>$error->errors()->all()]);
            }
            $image_name = rand().".".$image->getClientOriginalExtension();
            $image->move(public_path('images'),$image_name);
        }else{
            $rules = array(
                'first_name' => 'required',
                'last_name'  => 'required'
            );
            $error = Validator::make($request->all(),$rules);
            if($error->fails()){
                return response()->json(['errors'=>$error->errors()->all()]);
            }
        }
        $form_data = array(
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'image'      => $image_name
        );
        AjaxCrud::whereId($request->hidden_id)->update($form_data);
        return response()->json(['success'=>'Datos actualizados exitosamente.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = AjaxCrud::findOrFail($id); //este metodo regresara el modelo relacionado, basado en su P.K.
        $data->delete();
    }
}
