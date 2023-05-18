package com.example.ishindenshin;

import android.Manifest;
import android.content.Context;
import android.content.pm.PackageManager;
import android.content.res.AssetManager;
import android.database.Cursor;
import android.os.Bundle;
import android.util.Log;
import android.view.SurfaceView;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;

import org.opencv.android.BaseLoaderCallback;
import org.bytedeco.opencv.opencv_dnn.Net;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.nio.charset.StandardCharsets;

import android.annotation.SuppressLint;
import android.net.Uri;
import android.provider.MediaStore.Images.Media;
import android.app.Activity;
import android.content.Intent;
import android.view.Menu;
import android.view.View.OnClickListener;

public class MainActivity extends Activity implements OnClickListener {
    String[] UserData = new String[2];

    BaseLoaderCallback baseLoaderCallback;
    Net tinyYolo;
    Button yoloButton;
    int RESULT_PICK_FILENAME = 1;
    private SurfaceView surfaceView;
    private YoloView YoloView;
    String ServerURL = "https://web2-17423.azurewebsites.net/test/get_data.php";
    String name;
    EditText URL;
    String TempName, TempURL;

    private static final int REQUEST_EXTERNAL_STORAGE = 1;
    private static String[] PERMISSIONS_STORAGE = {Manifest.permission.READ_EXTERNAL_STORAGE, Manifest.permission.WRITE_EXTERNAL_STORAGE};

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        yoloButton = (Button)findViewById(R.id.yoloButton);
        yoloButton.setOnClickListener(this);

        URL = (EditText)findViewById(R.id.editText);

        ArrayAdapter<String> adapter = new ArrayAdapter<String>(this, android.R.layout.simple_spinner_item);
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        adapter.add("Anzai");
        adapter.add("Nakamura");
        adapter.add("Tsuji");
        adapter.add("Kinoshita");
        adapter.add("Hashidume");
        adapter.add("Manual");
        Spinner spinner = (Spinner) findViewById(R.id.spinner);
        spinner.setAdapter(adapter);

        spinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                Spinner spinner = (Spinner) parent;
                // 選択したアイテムを取得
                name = (String) spinner.getSelectedItem();

                // ログで確認
//                Log.i("i", name);
            }
            @Override
            public void onNothingSelected(AdapterView<?> arg0) {// アイテムを選択しなかったとき
            }
        });

        int permission = ContextCompat.checkSelfPermission(this, Manifest.permission.READ_EXTERNAL_STORAGE);
        if (permission != PackageManager.PERMISSION_GRANTED) {
            // We don't have permission so prompt the user
            ActivityCompat.requestPermissions(this, PERMISSIONS_STORAGE, REQUEST_EXTERNAL_STORAGE);
        }
    }

    @SuppressLint("ResourceType")
    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.layout.activity_main, menu);
        return true;
    }


    @Override
    protected void onResume() {
        super.onResume();
    }

    public void onClick(View v) {
        if(name.length() == 0){
            String text = "nameが未入力です";
            Toast.makeText(this, text, Toast.LENGTH_SHORT).show();
        }
        else if(URL.length() == 0){
            String text = "URLが未入力です";
            Toast.makeText(this, text, Toast.LENGTH_SHORT).show();
        }
        else{
            GetData();

            switch(v.getId()){
                case R.id.yoloButton:
                    pickFilenameFromGallery();
                    break;
            }
        }
    }

    public void GetData(){
//        TempName = name.getText().toString();
        TempURL = URL.getText().toString();
        byte[] sbyte = name.getBytes(StandardCharsets.UTF_8);
        String str = new String(sbyte);
        UserData[0] = str;
        UserData[1] = TempURL;
    }

    private void pickFilenameFromGallery() {
        Intent i = new Intent(Intent.ACTION_PICK, Media.EXTERNAL_CONTENT_URI);
        i.setType("video/*");
        startActivityForResult(i, RESULT_PICK_FILENAME);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if (requestCode == RESULT_PICK_FILENAME
                && resultCode == RESULT_OK
                && null != data) {
            Uri selectedImage = data.getData();
            String[] filePathColumn = { Media.DATA };

            Cursor cursor = getContentResolver().query(
                    selectedImage,
                    filePathColumn, null, null, null);
            cursor.moveToFirst();

            int columnIndex = cursor.getColumnIndex(filePathColumn[0]);
            String videoPath = cursor.getString(columnIndex); //動画のパス（文字列）
            cursor.close();

            Toast.makeText(this, videoPath, Toast.LENGTH_LONG).show();
//            Toast.makeText(this, "終了するまで触らないでください", Toast.LENGTH_LONG).show();
            //YOLOで物体検出
//            surfaceView = new YoloView(this, videoPath, UserData[0], UserData[1]);
//            setContentView(surfaceView);
            surfaceView = (SurfaceView)findViewById(R.id.SurfaceView);
            YoloView = new YoloView(this, surfaceView , videoPath, UserData[0], UserData[1]);
        }
    }

    // Upload file to storage and return a path.
    private static String getPath(String file, Context context) {
        AssetManager assetManager = context.getAssets();
        BufferedInputStream inputStream = null;
        try {
            // Read data from assets.
            inputStream = new BufferedInputStream(assetManager.open(file));
            byte[] data = new byte[inputStream.available()];
            inputStream.read(data);
            inputStream.close();
            // Create copy file in storage.
            File outFile = new File(context.getFilesDir(), file);
            FileOutputStream os = new FileOutputStream(outFile);
            os.write(data);
            os.close();
            // Return a path to file which may be read in common way.
            return outFile.getAbsolutePath();
        } catch (IOException ex) {
            Log.i("tag", "Failed to upload a file");
        }
        return "";
    }

    public void fin(){
        Toast.makeText(this, "終了しました。", Toast.LENGTH_LONG).show();
    }
}