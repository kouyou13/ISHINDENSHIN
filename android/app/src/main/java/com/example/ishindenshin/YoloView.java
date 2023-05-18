package com.example.ishindenshin;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.res.AssetManager;
import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.graphics.Paint;
import android.os.AsyncTask;
import android.util.Log;
import android.view.SurfaceHolder;
import android.view.SurfaceView;
import android.widget.Toast;

import org.bytedeco.javacv.*;
import org.bytedeco.javacv.FFmpegFrameGrabber;
import org.bytedeco.javacpp.*;
import org.bytedeco.opencv.opencv_core.*;
import org.bytedeco.opencv.opencv_dnn.Net;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

import static org.bytedeco.opencv.global.opencv_core.*;
import static org.bytedeco.opencv.global.opencv_dnn.NMSBoxes;
import static org.bytedeco.opencv.global.opencv_dnn.blobFromImage;
import static org.bytedeco.opencv.global.opencv_dnn.readNetFromDarknet;
import static org.bytedeco.opencv.global.opencv_imgproc.*;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;

@SuppressLint("ViewConstructor")
public class YoloView extends SurfaceView implements Runnable, SurfaceHolder.Callback {

    private Context Context = getContext();
    SurfaceHolder surfaceHolder;
    Thread thread;
    int screen_width, screen_height;
    String filepath;
    String ServerURL = "https://web2-17423.azurewebsites.net/ISHINDENSHIN/get_data.php";

    Net tinyYolo;

    static final int BALL_R = 30;
    int cx = BALL_R, cy = BALL_R;

    String[] UserData = new String[2];
//    Context[] Con = new Context[1];

    public YoloView(Context context, SurfaceView sv, String path, String name, String URL) {
        super(context);
        surfaceHolder = sv.getHolder();
        surfaceHolder.addCallback(this);
//        Log.i("YoloView", path);
        filepath = path;
        UserData[0] = name;
        UserData[1] = URL;
//        Con[0] = context;
//        Context = context;

        String tinyYoloCfg = getPath("yolov3-tiny.cfg", context);
        String tinyYoloWeights = getPath("yolov3-tiny_final.weights", context);

        tinyYolo = readNetFromDarknet(tinyYoloCfg, tinyYoloWeights);
    }

    @Override
    public void run() {
        int[] Xtl = new int[500]; //フレームごとのx座標左上を格納
        int[] Ytl = new int[500]; //フレームごとのy座標左上を格納
        int[] Xbr = new int[500]; //フレームごとのx座標右下を格納
        int[] Ybr = new int[500]; //フレームごとのy座標右下を格納
        double[] PerSecTempX = new double[4]; //0に向き判定、1~3に速度を格納
        double[] PerSecTempY = new double[3];
        double[] diameterPX = new double[11]; //0にサーブ開始フレーム番号、1総フレーム数、2~10にボールの直径[px]
        diameterPX[0] = 10000;
        diameterPX[1] = 0;
        double[] diameter = new double[10]; //ボールの直径[m]
        double[] ground = new double[4]; //0に測定したかの判断、1~2に地面のxy座標、3に身長+腕の高さ
        ground[0] = 0;
        int count=0;

        Canvas canvas = null;
        Bitmap bitmap = null;
        Paint paint = new Paint();

        while (thread != null) {

            File file = new File(filepath);

            FFmpegFrameGrabber grabber = new FFmpegFrameGrabber(file.getAbsolutePath());
            try {
                grabber.start();

                int i = grabber.getLengthInFrames();

                OpenCVFrameConverter.ToMat converter = new OpenCVFrameConverter.ToMat();
                AndroidFrameConverter converterToBitmap = new AndroidFrameConverter();

                Mat grabbedImage = new Mat();
                Frame frame = null;

                //フレームごとの切り取り
                while (grabber.getFrameNumber() < (grabber.getLengthInFrames() - 1)) {
                    int n = grabber.getFrameNumber(); //フレーム番号（カウンター）
                    frame = grabber.grabImage();

                    grabbedImage = converter.convert(frame);
                    int height = grabbedImage.rows();
                    int width = grabbedImage.cols();

                    grabbedImage = YoloMat(grabbedImage, n, Xtl , Ytl , Xbr , Ybr);
                    frame = converter.convert(grabbedImage);
                    bitmap = converterToBitmap.convert(frame);

                    canvas = surfaceHolder.lockCanvas();
                    canvas.drawBitmap(bitmap, 0, 0, paint);
                    surfaceHolder.unlockCanvasAndPost(canvas);

//                    Thread.sleep(30);

                    // ↓↓変更
                    Log.i("add","フレーム番号:" + n + " → " + (n+1));
                    if(n > 0 && Xtl[n]!=0 && (Xtl[n-1]!=0 || Xtl[n-2]!=0)) {
                        if (Xtl[n] != 0.0 && Xtl[n-1] != 0.0) {
                            PerSec(n, Xtl[n], Ytl[n], Xbr[n], Ybr[n], Xbr[n-1] - Xbr[n], Ybr[n-1] - Ybr[n], PerSecTempX, PerSecTempY, diameterPX, ground);
                        } else if (Xtl[n] == 0.0 && Xtl[n-1] != 0.0 && Xtl[n-2] != 0.0) { //座標が取れなかった時の処理
//                            PerSec(Xtl[n - 1], Ytl[n - 1], Xbr[n - 1], Ybr[n - 1], Xtl[n - 2] - Xtl[n - 1], Ytl[n - 2] - Ytl[n - 1], PerSecTempX, PerSecTempY, diameterPX);
//                            Log.i("add","座標が取れなかった");

                        }
                        else if (Xtl[n] != 0.0 && Xtl[n-1] == 0.0 && Xtl[n-2] != 0) { //座標が取れなかった時の次の処理
                            double lengthX = (Xbr[n-2] - Xbr[n]) / 2.0;
                            double lengthY = (Ybr[n-2] - Ybr[n]) / 2.0;
                            PerSec(n, Xtl[n], Ytl[n], Xbr[n], Ybr[n], lengthX, lengthY, PerSecTempX, PerSecTempY, diameterPX, ground);
//                            Log.i("add","座標が取れなかった次");
                        }
                        count = 0;
                    }

                    else if(Xtl[n] == 0){
                        Log.i("add","測定不可");
                        if(PerSecTempX[1] != 0){
                            count++; //ボールが画面外に行ったら処理終了
                            if(count > 10)
                                break;
                        }
                    }
                }
                grabber.stop();

                thread = null;

            } catch (Exception e) {
                e.printStackTrace();
            }

//            Log.i("finish","測定者:"+ UserData[0]);
            double height = ground[3];
//            Log.i("finish","地面からボールまでの高さ:"+height+"[m]");

            for(int p=3; p<9; p++){
                if(diameterPX[p] == 0)
                    diameterPX[p] = diameterPX[p-1];
                diameter[p-1] = 1108 * 0.16 / diameterPX[p]; // カメラからの距離[m]
            }

            double temp = 0.0;
            for(int k=3; k < 8; k++)
                temp += diameter[k] - diameter[k-1];

            double PerSecZ = temp / 5 * 30;

            double PerSecX=0.0;
            double PerSecY=0.0;
//            if(PerSecTempX[0] == 0)
//                Log.i("finish","サーブの向き：右");
//            else
//                Log.i("finish","サーブの向き：左");

            if(PerSecTempX[1] != 0 && PerSecTempX[2] != 0 && PerSecTempX[3] != 0){
                PerSecX = (PerSecTempX[1]+PerSecTempX[2]+PerSecTempX[3])/3;
                PerSecY = (PerSecTempY[0]+PerSecTempY[1]+PerSecTempY[2])/3;
                Log.i("finish","x軸の初速度："+ PerSecX + "[m/s]");
                Log.i("finish","y軸の初速度："+ PerSecY + "[m/s]");
            }

            else if(PerSecTempX[1] != 0 && PerSecTempX[2] != 0 && PerSecTempX[3] == 0){
                PerSecX = (PerSecTempX[1]+PerSecTempX[2])/2;
                PerSecY = (PerSecTempY[0]+PerSecTempY[1])/2;
                Log.i("finish","x軸の初速度："+ PerSecX + "[m/s]");
                Log.i("finish","y軸の初速度："+ PerSecY + "[m/s]");
            }

            String textZ;
            if(PerSecZ > 0){
                //奥に進む
                textZ = "z軸の初速度(奥)：";
                Log.i("finish",textZ + PerSecZ + "[m/s]");
            }

            else if(PerSecZ < 0){//手間に進む
                PerSecZ *= -1;
                textZ = "z軸の初速度(手前)：";
                Log.i("finish",textZ + PerSecZ + "[m/s]");
            }

            else{
                textZ = "z軸の初速度：";
                Log.i("finish",textZ + PerSecZ + "[m/s]");
            }

            //最高点の時の計算
//            double MaxHighTime = PerSecY / 9.8;
//            double LengthHighX = PerSecX * MaxHighTime;
//            double LengthHighZ = PerSecZ * MaxHighTime;
//            double LengthHighY = height + PerSecY * MaxHighTime - 4.9 * Math.pow(MaxHighTime,2);
//            Log.i("finish","最高点の時X："+LengthHighX+"[m]");
//            Log.i("finish","最高点の時Z："+LengthHighZ+"[m]");
//            Log.i("finish","最高点の時Y："+(LengthHighY+height)+"[m]");

            //ネット上の時の計算
            double NetTime = 9 / PerSecX;
            double LengthNetX = PerSecX * NetTime;
            double LengthNetZ = PerSecZ * NetTime;
            double LengthNetY = height + PerSecY * NetTime - 4.9 * Math.pow(NetTime,2);
//            Log.i("finish","ネット上の時X："+LengthNetX+"[m]");
//            Log.i("finish","ネット上の時Z："+LengthNetZ+"[m]");
            Log.i("finish","ネット上の時Y："+(LengthNetY+height)+"[m]");

            // 落下予測地点の計算
            double time=(PerSecY+Math.pow(Math.pow(PerSecY,2)+4*4.9*2.0,0.5))/9.8; //打ってから落下までの時間[s]
            double LengthX = PerSecX * time;
            double LengthZ = PerSecZ * time;
            Log.i("finish","落下予測地点：{" + LengthX + " , " + LengthZ + "}[m]");
            double LengthXpx = LengthX * 11;
            double LengthZpx = LengthZ * 11;
            Log.i("finish","落下予測地点：{" + LengthXpx + " , " + LengthZpx + "}[px]");

            //データベースに送信
            if(PerSecX != 0)
                InsertData(UserData[0], UserData[1], PerSecX, PerSecZ, (LengthNetY+height), LengthX, LengthZ, LengthXpx, LengthZpx);
        }
    }

    @Override
    public void surfaceChanged(SurfaceHolder holder, int format, int width, int height) {
        screen_width = width;
        screen_height = height;
    }

    @Override
    public void surfaceCreated(SurfaceHolder holder) {

        thread = new Thread(this);
        thread.start();
    }

    @Override
    public void surfaceDestroyed(SurfaceHolder holder) {
        thread = null;
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

    public Mat YoloMat(Mat frame , int n , int Xtl[], int Ytl[], int Xbr[], int Ybr[]) {

        Mat imageBlob = blobFromImage(frame, 0.00392, new Size(416, 416), new Scalar(0, 0, 0, 0),/*swapRB*/false, /*crop*/false, CV_32F);

        tinyYolo.setInput(imageBlob);

        // create output layers
        StringVector outNames = tinyYolo.getUnconnectedOutLayersNames();
        MatVector outs = new MatVector(outNames.size());

        // run detection
        tinyYolo.forward(outs, outNames);

        float confidenceThreshold = 0.5f;

        List<Integer> clsIds = new ArrayList<>();
        List<Float> confs = new ArrayList<>();
        List<Rect2d> rects = new ArrayList<>();

        for (int i = 0; i < outs.size(); ++i) {

            Mat result = outs.get(i);

            for (int j = 0; j < result.rows(); j++) {
                FloatPointer data = new FloatPointer(result.row(j).data());
                Mat scores = result.row(j).colRange(5, result.cols());

                Point classIdPoint = new Point(1);
                DoublePointer confidence = new DoublePointer(1);

                // Get the value and location of the maximum score
                minMaxLoc(scores, null, confidence, null, classIdPoint, null);
                if (confidence.get() > confidenceThreshold) {
                    // todo: maybe round instead of floor
                    int centerX = (int) (data.get(0) * frame.cols());
                    int centerY = (int) (data.get(1) * frame.rows());
                    int width = (int) (data.get(2) * frame.cols());
                    int height = (int) (data.get(3) * frame.rows());
                    int left = centerX - width / 2;
                    int top = centerY - height / 2;

                    clsIds.add((int) classIdPoint.x());
                    confs.add((float) confidence.get());
                    rects.add(new Rect2d(left, top, width, height));
                }
            }

        }
        int ArrayLength = confs.size();

        if (ArrayLength >= 1) {
            // Apply non-maximum suppression procedure.
            float nmsThresh = 0.2f;

//            Rect2d[] boxesArray = rects.toArray(new Rect2d[]);


            IntPointer indices = new IntPointer(confs.size());
            Rect2dVector boxes = new Rect2dVector();
            for(int i=0;i<rects.size();i++){
                boxes.push_back(rects.get(i));
            }

            FloatPointer con = new FloatPointer(confs.size());
            float[] cons = new float[confs.size()];
            for(int i=0;i<confs.size();i++){
                cons[i] = confs.get(i);
            }
            con.put(cons);

            NMSBoxes(boxes, con, confidenceThreshold, nmsThresh, indices);

            // Draw result boxes:
            for (int i = 0; i < indices.limit(); ++i) {

                int idx = indices.get(i);
                Rect2d box = rects.get(idx);

                int idGuy = clsIds.get(idx);

                float conf = confs.get(idx);

                List<String> cocoNames = Arrays.asList("volleyball");

                int intConf = (int) (conf * 100);

                putText(frame, cocoNames.get(idGuy) + " " + intConf + "%", new Point((int)box.tl().x(),(int)box.tl().y()), FONT_HERSHEY_SIMPLEX, 1, new Scalar(0, 0, 255, 0));

                rectangle(frame, new Point((int)box.tl().x(),(int)box.tl().y()), new Point((int)box.br().x(),(int)box.br().y()), new Scalar(0, 0, 255, 0));

                if(i == 0){
                    Xtl[n] = (int)box.tl().x();
                    Ytl[n] = (int)box.tl().y();
                    Xbr[n] = (int)box.br().x();
                    Ybr[n] = (int)box.br().y();
                }
                else if(i > 0){
                    if(Math.abs(Xtl[n-1]-Xtl[n]) > Math.abs(Xtl[n-1]-(int)box.tl().x())){ //二つ以上認識したときの処理(?)
                        Xtl[n] = (int)box.tl().x();
                        Ytl[n] = (int)box.tl().y();
                        Xbr[n] = (int)box.br().x();
                        Ybr[n] = (int)box.br().y();
                    }
                }
            }
        }
        return frame;
    }

    // ↓↓変更
    //速度m/sを出す
    public void PerSec(int FrameNum, int Xtl, int Ytl, int Xbr, int Ybr, double lengthX, double lengthY, double PerSecTempX[], double PerSecTempY[], double diameterPX[], double ground[]) {
//        double lengthX; //1フレームのx軸の移動距離(px)
//        double lengthY; //1フレームのy軸の移動距離(px)
        double PerSecX; //x軸方向の速度[m/s]
        double PerSecY; //y軸方向の速度[m/s]

        PerSecX = lengthX / (Xbr - Xtl) * 0.2 * 30;
        PerSecY = lengthY / (Ybr - Ytl) * 0.2 * 30;
        double v = (Xbr - Xtl + Ybr - Ytl) / 2.0;

        if(ground[0] == 0 && lengthY>0){ //バウンドでボールがはねた時の座標
            ground[0] = 1;
            ground[1] = Xbr;
            ground[2] = Ybr;
        }

        if((PerSecX > 7 && PerSecX <= 20) || (PerSecX < -7 && PerSecX >= -20)){ // 向き（試し)

            if(lengthX < 0){ //右
                PerSecTempX[0] = 0;
                if(PerSecTempX[1] == 0 && PerSecTempX[2] == 0 && PerSecTempX[3] == 0){
                    Log.i("add" , "サーブ打ち始めた");
                    PerSecTempX[1] = PerSecX*-1;
                    PerSecTempY[0] = PerSecY;
                    diameterPX[0] = FrameNum;
                    ground[3] = 1108 * 0.16 / (ground[2] - Ybr); //[m]
                }

                else if(PerSecTempX[1] != 0 && PerSecTempX[2] == 0 && PerSecTempX[3] == 0){
                    PerSecTempX[2] = PerSecX*-1;
                    PerSecTempY[1] = PerSecY;

                }
                else if(PerSecTempX[1] != 0 && PerSecTempX[2] != 0 && PerSecTempX[3] == 0){
                    PerSecTempX[3] = PerSecX*-1;
                    PerSecTempY[2] = PerSecY;

                }
            }

            else if(lengthX > 0){ //左
                PerSecTempX[0] = 1;
                if(PerSecTempX[1] == 0 && PerSecTempX[2] == 0 && PerSecTempX[3] == 0){
                    Log.i("add" , "サーブ打ち始めた");
                    PerSecTempX[1] = PerSecX;
                    PerSecTempY[0] = PerSecY;
                    diameterPX[0] = FrameNum;
                    ground[3] = 1108 * 0.16 / (ground[2] - Ybr); //[m]
                }

                else if(PerSecTempX[1] != 0 && PerSecTempX[2] == 0 && PerSecTempX[3] == 0){
                    PerSecTempX[2] = PerSecX;
                    PerSecTempY[1] = PerSecY;
                }

                else if(PerSecTempX[1] != 0 && PerSecTempX[2] != 0 && PerSecTempX[3] == 0){
                    PerSecTempX[3] = PerSecX;
                    PerSecTempY[2] = PerSecY;
                }
            }
        }

        if(diameterPX[0] <= FrameNum && FrameNum <= diameterPX[0] + 8){
            diameterPX[FrameNum - (int)diameterPX[0] + 2] = v;
            diameterPX[1] += 1;
        }
    }

    //DBにデータ送信
    public void InsertData(final String name, final String URL,final double PerSecX,final double PerSecZ,final double LengthNetY,final double LengthX,final double LengthZ,final double LengthXpx, final double LengthZpx){

        final String ParsecX = String.valueOf(PerSecX);
        final String ParsecZ = String.valueOf(PerSecZ);
        final String lengthNetY = String.valueOf(LengthNetY);
        final String lengthX = String.valueOf(LengthX);
        final String lengthZ = String.valueOf(LengthZ);
        final String lengthXpx = String.valueOf(LengthXpx);
        final String lengthZpx = String.valueOf(LengthZpx);

        class SendPostReqAsyncTask extends AsyncTask<String, Void, String> {
            @Override
            protected String doInBackground(String... params) {

                List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>();

                final String ParsecX = String.valueOf(PerSecX);
                final String ParsecZ = String.valueOf(PerSecZ);
                final String lengthNetY = String.valueOf(LengthNetY);
                final String lengthX = String.valueOf(LengthX);
                final String lengthZ = String.valueOf(LengthZ);
                final String lengthXpx = String.valueOf(LengthXpx);
                final String lengthZpx = String.valueOf(LengthZpx);
                
                nameValuePairs.add(new BasicNameValuePair("name", name));
                nameValuePairs.add(new BasicNameValuePair("URL", URL));
                nameValuePairs.add(new BasicNameValuePair("ParsecX", ParsecX));
                nameValuePairs.add(new BasicNameValuePair("ParsecZ", ParsecZ));
                nameValuePairs.add(new BasicNameValuePair("lengthNetY", lengthNetY));
                nameValuePairs.add(new BasicNameValuePair("lengthX", lengthX));
                nameValuePairs.add(new BasicNameValuePair("lengthZ", lengthZ));
                nameValuePairs.add(new BasicNameValuePair("lengthXpx", lengthXpx));
                nameValuePairs.add(new BasicNameValuePair("lengthZpx", lengthZpx));

                try {
                    HttpClient httpClient = new DefaultHttpClient();
                    HttpPost httpPost = new HttpPost(ServerURL);
                    httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs));
                    HttpResponse httpResponse = httpClient.execute(httpPost);
                    HttpEntity httpEntity = httpResponse.getEntity();
                } catch (ClientProtocolException e) {

                } catch (IOException e) {

                }
                return "Data Inserted Successfully";
            }

            @Override
            protected void onPostExecute(String result) {
                super.onPostExecute(result);
            }
        }
        SendPostReqAsyncTask sendPostReqAsyncTask = new SendPostReqAsyncTask();
        sendPostReqAsyncTask.execute(name, URL, ParsecX, ParsecZ, lengthNetY, lengthX, lengthZ, lengthXpx, lengthZpx);
//        Toast.makeText(Context, "解析が終了しました", Toast.LENGTH_LONG).show();
    }
}